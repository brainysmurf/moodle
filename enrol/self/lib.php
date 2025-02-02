<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	 See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Self enrolment plugin.
 *
 * @package	   enrol_self
 * @copyright  2010 Petr Skoda	{@link http://skodak.org}
 * @license	   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Self enrolment plugin implementation.
 * @author Petr Skoda
 * @license	  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_self_plugin extends enrol_plugin {

	protected $lasternoller = null;
	protected $lasternollerinstanceid = 0;

	/**
	 * Returns optional enrolment information icons.
	 *
	 * This is used in course list for quick overview of enrolment options.
	 *
	 * We are not using single instance parameter because sometimes
	 * we might want to prevent icon repetition when multiple instances
	 * of one type exist. One instance may also produce several icons.
	 *
	 * @param array $instances all enrol instances of this type in one course
	 * @return array of pix_icon
	 */
	public function get_info_icons(array $instances) {
		$key = false;
		$nokey = false;
		foreach ($instances as $instance) {
			if (!$instance->customint6) {
				// New enrols not allowed.
				continue;
			}
			if ($instance->password or $instance->customint1) {
				$key = true;
			} else {
				$nokey = true;
			}
		}
		$icons = array();
		if ($nokey) {
			$icons[] = new pix_icon('withoutkey', get_string('pluginname', 'enrol_self'), 'enrol_self');
		}
		if ($key) {
			$icons[] = new pix_icon('withkey', get_string('pluginname', 'enrol_self'), 'enrol_self');
		}
		return $icons;
	}

	/**
	 * Returns localised name of enrol instance
	 *
	 * @param stdClass $instance (null is accepted too)
	 * @return string
	 */
	public function get_instance_name($instance) {
		global $DB;

		if (empty($instance->name)) {
			if (!empty($instance->roleid) and $role = $DB->get_record('role', array('id'=>$instance->roleid))) {
				$role = ' (' . role_get_name($role, context_course::instance($instance->courseid, IGNORE_MISSING)) . ')';
			} else {
				$role = '';
			}
			$enrol = $this->get_name();
			return get_string('pluginname', 'enrol_'.$enrol) . $role;
		} else {
			return format_string($instance->name);
		}
	}

	public function roles_protected() {
		// Users may tweak the roles later.
		return false;
	}

	public function allow_unenrol(stdClass $instance) {
		// Users with unenrol cap may unenrol other users manually manually.
		return true;
	}

	public function allow_manage(stdClass $instance) {
		// Users with manage cap may tweak period and status.
		return true;
	}

	public function show_enrolme_link(stdClass $instance) {
		global $CFG, $USER;

		if ($instance->status != ENROL_INSTANCE_ENABLED) {
			return false;
		}

		if (!$instance->customint6) {
			// New enrols not allowed.
			return false;
		}

		if ($instance->customint5) {
			require_once("$CFG->dirroot/cohort/lib.php");
			return cohort_is_member($instance->customint5, $USER->id);
		}
		return true;
	}

	/**
	 * Sets up navigation entries.
	 *
	 * @param stdClass $instancesnode
	 * @param stdClass $instance
	 * @return void
	 */
	public function add_course_navigation($instancesnode, stdClass $instance) {
		if ($instance->enrol !== 'self') {
			 throw new coding_exception('Invalid enrol instance type!');
		}

		$context = context_course::instance($instance->courseid);
		if (has_capability('enrol/self:config', $context)) {
			$managelink = new moodle_url('/enrol/self/edit.php', array('courseid'=>$instance->courseid, 'id'=>$instance->id));
			$instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);
		}

		/*
		* We want parents to be able to unenrol their children from activities (if they were allowed to enrol them to start with)
		* Add links to do this in the course administration menu in the awesomebar
		*/

		global $SESSION;

		//If the current user is a parent, and the current course allows parents to enrol their children, and the current user has at least 1 child...
		if ($SESSION->userIsParent && $instance->customint8 && count($SESSION->usersChildren) > 0) {

			$showEnrolMoreChildrenLink = false;
			foreach ($SESSION->usersChildren as $child) {

				//Is this child enrolled in the course?
				if(enrol_user_is_enrolled($child->userid, $instance->id)) {

					//If the user is enrolled, add a link for the parent to unenrol the child
					$str = "Remove {$child->firstname} {$child->lastname} from activity";
					$instancesnode->parent->parent->add($str, "/enrol/self/unenrolchild.php?enrolid={$instance->id}&childuserid={$child->userid}", navigation_node::TYPE_SETTING);
					/*
						About the parent->parent thing...
						If we just used this to add the menu item:
						$instancesnode->add('Testing', '#', navigation_node::TYPE_SETTING);
						then the link would get added into a submenu for this enrolment plugin (like this http://ctrlv.in/262781)
						So we add it to the parent's parent so it goes into the main menu (like this http://ctrlv.in/262782)
					*/
				} else {
					//This child isn't enrolled, so show the link to the enrol page for this course
					$showEnrolMoreChildrenLink = true;
				}
			}

			if ($showEnrolMoreChildrenLink) {
				$instancesnode->parent->parent->add('Enrol Children page', "/enrol/index.php?id={$instance->courseid}", navigation_node::TYPE_SETTING);
			}
		}

	}

	/**
	 * Returns edit icons for the page with list of instances
	 * @param stdClass $instance
	 * @return array
	 */
	public function get_action_icons(stdClass $instance) {
		global $OUTPUT;

		if ($instance->enrol !== 'self') {
			throw new coding_exception('invalid enrol instance!');
		}
		$context = context_course::instance($instance->courseid);

		$icons = array();

		if (has_capability('enrol/self:config', $context)) {
			$editlink = new moodle_url("/enrol/self/edit.php", array('courseid'=>$instance->courseid, 'id'=>$instance->id));
			$icons[] = $OUTPUT->action_icon($editlink, new pix_icon('t/edit', get_string('edit'), 'core',
				array('class' => 'iconsmall')));
		}

		return $icons;
	}

	/**
	 * Returns link to page which may be used to add new instance of enrolment plugin in course.
	 * @param int $courseid
	 * @return moodle_url page url
	 */
	public function get_newinstance_link($courseid) {
		$context = context_course::instance($courseid, MUST_EXIST);

		if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/self:config', $context)) {
			return NULL;
		}
		// Multiple instances supported - different roles with different password.
		return new moodle_url('/enrol/self/edit.php', array('courseid'=>$courseid));
	}

	/**
	 * Creates course enrol form, checks if form submitted
	 * and enrols user if necessary. It can also redirect.
	 *
	 * @param stdClass $instance
	 * @return string html text, usually a form in a text box
	 */
	public function enrol_page_hook(stdClass $instance) {
		global $CFG, $OUTPUT, $SESSION, $USER, $DB;

		if (isguestuser()) {
			// Can not enrol guest!!
			return null;
		}

		//Enrollments don't start yet
		if ($instance->enrolstartdate != 0 and $instance->enrolstartdate > time()) {
			return $OUTPUT->errorbox("Sorry, you can't join this activity until " . date('l M jS Y', $instance->enrolstartdate));
		}

		//Enrollments have ended
		if ($instance->enrolenddate != 0 and $instance->enrolenddate < time()) {
			return $OUTPUT->errorbox("Sorry, you can't join this activity after " . date('l M jS Y', $instance->enrolenddate));
		}

		//Allow new enrollments is set to 'no'
		if (!$instance->customint6) {
			return $OUTPUT->errorbox("Sorry, enrollments for this activity are closed.");
			// New enrols not allowed.
			return null;
		}

		//Max enrol limit specified.
		if ($instance->customint3 > 0) {
			// This old way just returns the total number of people enrolled via this enrolment method
			#$count = $DB->count_records('user_enrolments', array('enrolid'=>$instance->id));

			// The new way counts the number of people enroled as student/participant
			$q = 'select count(*) from {user_enrolments} ue
			join {enrol} enrl on enrl.id = ue.enrolid
			join {context} ctx on ctx.instanceid = enrl.courseid and ctx.contextlevel = 50
			join {role_assignments} ra on ra.userid = ue.userid and ra.contextid = ctx.id
			where ue.enrolid = ? and ra.roleid = 5'; // 5 = student role id
			$count = $DB->get_field_sql($q, array($instance->id));

			if ($count >= $instance->customint3) {
				//Too many people enrolled already
				// This is the only check that is done to limit number of participants!
				// Making it easy to tweak, but easy for somebody to work around if they know
				// what they're doing...
				return $OUTPUT->errorbox("Sorry, this activity already has the maximum number of participants ({$count}).");
			}
		}

		require_once("$CFG->dirroot/enrol/self/locallib.php");
		require_once("$CFG->dirroot/group/lib.php");

		$form = new enrol_self_enrol_form(NULL, $instance);
		$instanceid = optional_param('instance', 0, PARAM_INT);

		if ($instance->id == $instanceid) {

			if ($data = $form->get_data()) {

				//User has submitted the self enrolment form (clicked the enrol my child or join activity button)

				$enrol = enrol_get_plugin('self');
				$timestart = time();
				if ($instance->enrolperiod) {
					$timeend = $timestart + $instance->enrolperiod;
				} else {
					$timeend = 0;
				}

				$userids_to_enrol = array();

				if (isset($data->enrolchildsubmit)) { //Parent wants to enrol their child instead of theirself

					//Enrol the children
					foreach ($data->enrolchilduserids as $userid => $one) {
						$userids_to_enrol[] = $userid;
					}

					//We also want to enrol the parent
					//Dec 9th - don't need to explicitly do this here anymore because the parent will be enrol when the student is enrolled
					//by the enrol_user method anyway
					//$this->enrol_user($instance, $USER->id, 12, $timestart, $timeend);

				} else { //Enrol the current user
					$userids_to_enrol[] = $USER->id;
				}


				foreach ($userids_to_enrol as $userid_to_enrol) {

					 $this->enrol_user($instance, $userid_to_enrol, $instance->roleid, $timestart, $timeend);
					 	 //TODO: There should be userid somewhere!
					 add_to_log($instance->courseid, 'course', 'enrol', '../enrol/users.php?id='.$instance->courseid, $instance->courseid);

					  if ($instance->password and $instance->customint1 and $data->enrolpassword !== $instance->password) {
						 // it must be a group enrolment, let's assign group too
						 $groups = $DB->get_records('groups', array('courseid'=>$instance->courseid), 'id', 'id, enrolmentkey');
						 foreach ($groups as $group) {
							 if (empty($group->enrolmentkey)) {
								 continue;
							 }
							 if ($group->enrolmentkey === $data->enrolpassword) {
								 groups_add_member($group->id, $userid_to_enrol);
								 break;
							 }
						 }
					 }

					// Send welcome message.
					 if ($instance->customint4) {
						 //$this->email_welcome_message($instance, $USER);
					 }
				}

				// Save bus preferences

				// Using $_POST instead of $data because Moodle doesn't
				// put form fields into the $data object unless they were added
				// to the form using the API. The bus checkbox is added manually to make it
				// appear inline next to the user.
				if (isset($_POST['bus'])) {
					foreach ($_POST['bus'] as $userid => $bus) {
						$bus = (bool)$bus;
						SSIS::setUserActivityBusRequirement($userid, $bus);
					}
				}

				if (count($userids_to_enrol) > 0) {
					//We want to refresh the page to show changes
					redirect("/enrol/index.php?id={$instance->courseid}");
				}

			}
		}

		ob_start();
		$form->display();
		$output = ob_get_clean();

		return $output;
		//return $OUTPUT->box($output);
	}

	/**
	 * Add new instance of enrol plugin with default settings.
	 * @param stdClass $course
	 * @return int id of new instance
	 */
	public function add_default_instance($course) {
		$fields = $this->get_instance_defaults();

		if ($this->get_config('requirepassword')) {
			$fields['password'] = generate_password(20);
		}

		return $this->add_instance($course, $fields);
	}

	/**
	 * Returns defaults for new instances.
	 * @return array
	 */
	public function get_instance_defaults() {
		$expirynotify = $this->get_config('expirynotify');
		if ($expirynotify == 2) {
			$expirynotify = 1;
			$notifyall = 1;
		} else {
			$notifyall = 0;
		}

		$fields = array();
		$fields['status']		   = $this->get_config('status');
		$fields['roleid']		   = $this->get_config('roleid');
		$fields['enrolperiod']	   = $this->get_config('enrolperiod');
		$fields['expirynotify']	   = $expirynotify;
		$fields['notifyall']	   = $notifyall;
		$fields['expirythreshold'] = $this->get_config('expirythreshold');
		$fields['customint1']	   = $this->get_config('groupkey');
		$fields['customint2']	   = $this->get_config('longtimenosee');
		$fields['customint3']	   = $this->get_config('maxenrolled');
		$fields['customint4']	   = $this->get_config('sendcoursewelcomemessage');
		$fields['customint5']	   = 0;
		$fields['customint6']	   = $this->get_config('newenrols');

		return $fields;
	}

	/**
	 * Send welcome email to specified user.
	 *
	 * @param stdClass $instance
	 * @param stdClass $user user record
	 * @return void
	 */
	protected function email_welcome_message($instance, $user) {
		global $CFG, $DB;

		$course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);
		$context = context_course::instance($course->id);

		$a = new stdClass();
		$a->coursename = format_string($course->fullname, true, array('context'=>$context));
		$a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id&course=$course->id";

		if (trim($instance->customtext1) !== '') {
			$message = $instance->customtext1;
			$message = str_replace('{$a->coursename}', $a->coursename, $message);
			$message = str_replace('{$a->profileurl}', $a->profileurl, $message);
			if (strpos($message, '<') === false) {
				// Plain text only.
				$messagetext = $message;
				$messagehtml = text_to_html($messagetext, null, false, true);
			} else {
				// This is most probably the tag/newline soup known as FORMAT_MOODLE.
				$messagehtml = format_text($message, FORMAT_MOODLE, array('context'=>$context, 'para'=>false, 'newlines'=>true, 'filter'=>true));
				$messagetext = html_to_text($messagehtml);
			}
		} else {
			$messagetext = get_string('welcometocoursetext', 'enrol_self', $a);
			$messagehtml = text_to_html($messagetext, null, false, true);
		}

		$subject = get_string('welcometocourse', 'enrol_self', format_string($course->fullname, true, array('context'=>$context)));

		$rusers = array();
		if (!empty($CFG->coursecontact)) {
			$croles = explode(',', $CFG->coursecontact);
			list($sort, $sortparams) = users_order_by_sql('u');
			$rusers = get_role_users($croles, $context, true, '', 'r.sortorder ASC, ' . $sort, null, '', '', '', '', $sortparams);
		}
		if ($rusers) {
			$contact = reset($rusers);
		} else {
			$contact = generate_email_supportuser();
		}

		// Directly emailing welcome message rather than using messaging.
		email_to_user($user, $contact, $subject, $messagetext, $messagehtml);
	}

	/**
	 * Enrol self cron support.
	 * @return void
	 */
	public function cron() {
		$trace = new text_progress_trace();
		$this->sync($trace, null);
		$this->send_expiry_notifications($trace);
	}

	/**
	 * Sync all meta course links.
	 *
	 * @param progress_trace $trace
	 * @param int $courseid one course, empty mean all
	 * @return int 0 means ok, 1 means error, 2 means plugin disabled
	 */
	public function sync(progress_trace $trace, $courseid = null) {
		global $DB;

		if (!enrol_is_enabled('self')) {
			$trace->finished();
			return 2;
		}

		// Unfortunately this may take a long time, execution can be interrupted safely here.
		@set_time_limit(0);
		raise_memory_limit(MEMORY_HUGE);

		$trace->output('Verifying self-enrolments...');

		$params = array('now'=>time(), 'useractive'=>ENROL_USER_ACTIVE, 'courselevel'=>CONTEXT_COURSE);
		$coursesql = "";
		if ($courseid) {
			$coursesql = "AND e.courseid = :courseid";
			$params['courseid'] = $courseid;
		}

		// Note: the logic of self enrolment guarantees that user logged in at least once (=== u.lastaccess set)
		//		 and that user accessed course at least once too (=== user_lastaccess record exists).

		// First deal with users that did not log in for a really long time - they do not have user_lastaccess records.
		$sql = "SELECT e.*, ue.userid
				  FROM {user_enrolments} ue
				  JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'self' AND e.customint2 > 0)
				  JOIN {user} u ON u.id = ue.userid
				 WHERE :now - u.lastaccess > e.customint2
					   $coursesql";
		$rs = $DB->get_recordset_sql($sql, $params);
		foreach ($rs as $instance) {
			$userid = $instance->userid;
			unset($instance->userid);
			$this->unenrol_user($instance, $userid);
			$days = $instance->customint2 / 60*60*24;
			$trace->output("unenrolling user $userid from course $instance->courseid as they have did not log in for at least $days days", 1);
		}
		$rs->close();

		// Now unenrol from course user did not visit for a long time.
		$sql = "SELECT e.*, ue.userid
				  FROM {user_enrolments} ue
				  JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'self' AND e.customint2 > 0)
				  JOIN {user_lastaccess} ul ON (ul.userid = ue.userid AND ul.courseid = e.courseid)
				 WHERE :now - ul.timeaccess > e.customint2
					   $coursesql";
		$rs = $DB->get_recordset_sql($sql, $params);
		foreach ($rs as $instance) {
			$userid = $instance->userid;
			unset($instance->userid);
			$this->unenrol_user($instance, $userid);
				$days = $instance->customint2 / 60*60*24;
			$trace->output("unenrolling user $userid from course $instance->courseid as they have did not access course for at least $days days", 1);
		}
		$rs->close();

		$trace->output('...user self-enrolment updates finished.');
		$trace->finished();

		$this->process_expirations($trace, $courseid);

		return 0;
	}

	/**
	 * Returns the user who is responsible for self enrolments in given instance.
	 *
	 * Usually it is the first editing teacher - the person with "highest authority"
	 * as defined by sort_by_roleassignment_authority() having 'enrol/self:manage'
	 * capability.
	 *
	 * @param int $instanceid enrolment instance id
	 * @return stdClass user record
	 */
	protected function get_enroller($instanceid) {
		global $DB;

		if ($this->lasternollerinstanceid == $instanceid and $this->lasternoller) {
			return $this->lasternoller;
		}

		$instance = $DB->get_record('enrol', array('id'=>$instanceid, 'enrol'=>$this->get_name()), '*', MUST_EXIST);
		$context = context_course::instance($instance->courseid);

		if ($users = get_enrolled_users($context, 'enrol/self:manage')) {
			$users = sort_by_roleassignment_authority($users, $context);
			$this->lasternoller = reset($users);
			unset($users);
		} else {
			$this->lasternoller = parent::get_enroller($instanceid);
		}

		$this->lasternollerinstanceid = $instanceid;

		return $this->lasternoller;
	}

	/**
	 * Gets an array of the user enrolment actions.
	 *
	 * @param course_enrolment_manager $manager
	 * @param stdClass $ue A user enrolment object
	 * @return array An array of user_enrolment_actions
	 */
	public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
		$actions = array();
		$context = $manager->get_context();
		$instance = $ue->enrolmentinstance;
		$params = $manager->get_moodlepage()->url->params();
		$params['ue'] = $ue->id;
		if ($this->allow_unenrol($instance) && has_capability("enrol/self:unenrol", $context)) {
			$url = new moodle_url('/enrol/unenroluser.php', $params);
			$actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class'=>'unenrollink', 'rel'=>$ue->id));
		}
		if ($this->allow_manage($instance) && has_capability("enrol/self:manage", $context)) {
			$url = new moodle_url('/enrol/editenrolment.php', $params);
			$actions[] = new user_enrolment_action(new pix_icon('t/edit', ''), get_string('edit'), $url, array('class'=>'editenrollink', 'rel'=>$ue->id));
		}
		return $actions;
	}

	/**
	 * Restore instance and map settings.
	 *
	 * @param restore_enrolments_structure_step $step
	 * @param stdClass $data
	 * @param stdClass $course
	 * @param int $oldid
	 */
	public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid) {
		global $DB;
		if ($step->get_task()->get_target() == backup::TARGET_NEW_COURSE) {
			$merge = false;
		} else {
			$merge = array(
				'courseid'	 => $data->courseid,
				'enrol'		 => $this->get_name(),
				'roleid'	 => $data->roleid,
			);
		}
		if ($merge and $instances = $DB->get_records('enrol', $merge, 'id')) {
			$instance = reset($instances);
			$instanceid = $instance->id;
		} else {
			if (!empty($data->customint5)) {
				if ($step->get_task()->is_samesite()) {
					// Keep cohort restriction unchanged - we are on the same site.
				} else {
					// Use some id that can not exist in order to prevent self enrolment,
					// because we do not know what cohort it is in this site.
					$data->customint5 = -1;
				}
			}
			$instanceid = $this->add_instance($course, (array)$data);
		}
		$step->set_mapping('enrol', $oldid, $instanceid);
	}

	/**
	 * Restore user enrolment.
	 *
	 * @param restore_enrolments_structure_step $step
	 * @param stdClass $data
	 * @param stdClass $instance
	 * @param int $oldinstancestatus
	 * @param int $userid
	 */
	public function restore_user_enrolment(restore_enrolments_structure_step $step, $data, $instance, $userid, $oldinstancestatus) {
		$this->enrol_user($instance, $userid, null, $data->timestart, $data->timeend, $data->status);
	}

	/**
	 * Restore role assignment.
	 *
	 * @param stdClass $instance
	 * @param int $roleid
	 * @param int $userid
	 * @param int $contextid
	 */
	public function restore_role_assignment($instance, $roleid, $userid, $contextid) {
		// This is necessary only because we may migrate other types to this instance,
		// we do not use component in manual or self enrol.
		role_assign($roleid, $userid, $contextid, '', 0);
	}


	public function enrol_user(stdClass $instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = null, $recovergrades = null)
	{
		parent::enrol_user($instance, $userid, $roleid, $timestart, $timeend, $status, $recovergrades);

		//If this course allows parents to enrol students, we want to enrol the parent if they're not already
		if ($instance->customint8) {

			//Get the user's parents
			$parents = get_users_parents($userid);
			foreach ($parents as $parent) {
				//Check if parent is enrolled
				if (!enrol_user_is_enrolled($parent->userid, $instance->id)) {
					$this->enrol_user($instance, $parent->userid, 12);
				}
			}

		}

		global $USER;
		if ($USER->id == $userid) {
			global $OUTPUT;
			$OUTPUT->refresh_awesomebar();
		}
	}

	public function unenrol_user(stdClass $instance, $userid)
	{
		//Unenrol the person
		parent::unenrol_user($instance, $userid);
		// ^ doesn't return anything so we have to assume it worked

		//If this course allows parents to enrol students, we want to unenrol parents when their child leaves the activity
		//(Either the parent unenrolled them or the child unenrolled theirself)
		if ($instance->customint8) {

			//Get the user's parents
			$parents = get_users_parents($userid);

			foreach ($parents as $parent) {
				//Check if parent is enrolled
				if (enrol_user_is_enrolled($parent->userid, $instance->id)) {

					//Check if the parent still has other children who are enrolled
					$children = get_users_children($parent->userid);
					foreach ($children as $child) {
						if (enrol_user_is_enrolled($child->userid, $instance->id)) {
							//Child is still enrolled - quit
							return;
						}
					}

					//User has no children or all of their children are unenrolled - unenrol the parent
					$this->unenrol_user($instance, $parent->userid);

				}
			}
		}

		global $USER;
		if ($USER->id == $userid) {
			global $OUTPUT;
			$OUTPUT->refresh_awesomebar();
		}
	}

}
