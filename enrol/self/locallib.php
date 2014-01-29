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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Self enrol plugin implementation.
 *
 * @package    enrol_self
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class enrol_self_enrol_form extends moodleform {
    protected $instance;
    protected $toomany = false;

    /**
     * Overriding this function to get unique form id for multiple self enrolments.
     *
     * @return string form identifier
     */
    protected function get_form_identifier() {
        $formid = $this->_customdata->id.'_'.get_class($this);
        return $formid;
    }

    public function definition() {
        global $DB, $CFG, $OUTPUT, $SESSION, $USER;
		require_once("$CFG->dirroot/cohort/lib.php");

        $mform = $this->_form;
        $instance = $this->_customdata;
        $this->instance = $instance;
        $plugin = enrol_get_plugin('self');

		//Enrollment requires a password - show the password box       
        if ($instance->password) {
            // Change the id of self enrolment key input as there can be multiple self enrolment methods.
            $mform->addElement('passwordunmask', 'enrolpassword', get_string('password', 'enrol_self'),
                    array('id' => 'enrolpassword_'.$instance->id));
        } else {
            //$mform->addElement('static', 'nokey', '', get_string('nopassword', 'enrol_self'));
        }


        //Enrollments are limited to a certain cohort
        if ($instance->customint5) {
			$mustBeInCohort = $DB->get_record('cohort', array('id'=>$instance->customint5));
	        if (!$mustBeInCohort) {
	            return null;
	        }
	        $niceCohortName = strtolower($mustBeInCohort->name);
	        $niceCohortName = preg_replace('/(^all )/i','',$niceCohortName);
        } else {
        	$mustBeInCohort = false;
        }


		/*
		* Parent enrolling child
		*/

		//If user is a parent and parents can enrol children
		if ($instance->customint8 && $SESSION->userIsParent) {

			if (!empty($SESSION->usersChildren) && $children = $SESSION->usersChildren) {
				$enrollingChildren = true;
				
				$mform->addElement('header', 'enrolchildsection', 'Enrol your child in this activity');
				
				$mform->addElement('html', '<div class="helptext">Tick which of your children you want to enrol in this activity and then click <strong>Enrol My Child</strong></div>');
				
				$options = array();
				foreach($children as $child) {
				
					$name = $child->firstname.' '.$child->lastname;
				
					if (enrol_user_is_enrolled($child->userid, $instance->id)) { 
						
						//User is already enrolled
						$mform->addElement('checkbox', "enrolchilduserids[{$child->userid}]", $name, '<span class="green"><i class="ok-sign"></i> Enrolled!&nbsp;&nbsp;(Go to the "Course Administration" menu above to remove.)</span>', array('disabled'=>'disabled', 'class'=>'enrolchildcheckbox'));
			
			        } else if ($mustBeInCohort && !cohort_is_member($mustBeInCohort->id, $child->userid)) {
			        
			        	//Child isn't in the required cohort
						$mform->addElement('checkbox', "enrolchilduserids[{$child->userid}]", $name, "<span class=\"red\"><i class=\"icon-warning-sign\"></i> Can't be enrolled because only <strong>{$niceCohortName}</strong> can join.</span>", array('disabled'=>'disabled', 'class'=>'enrolchildcheckbox'));
			        
			        } else {
			        	
			        	//User can be enrolled
						$mform->addElement('checkbox', "enrolchilduserids[{$child->userid}]", $name, '<span class="grey"></span>', array('class'=>'enrolchildcheckbox'));
						
			        }
				
				}
				
				//Enrol my child button
				$mform->addElement('submit', 'enrolchildsubmit', get_string('enrolchild', 'enrol_self'), array('class' => 'dnet-disabled'));
				
				//A .dnet-disabled class instead of the disabled attribute is used so click events can be bound to the button
				$mform->addElement('html', '<script>
				
					$(document).on("change","input.enrolchildcheckbox",function()
					{
						var count = $("input.enrolchildcheckbox:checked").length;
						if (count > 0) {
							$("#id_enrolchildsubmit").removeClass("dnet-disabled");
						} else {
							$("#id_enrolchildsubmit").addClass("dnet-disabled");
						}
					});
					
					$(document).on("click","#id_enrolchildsubmit",function()
					{
						if ($(this).hasClass("dnet-disabled")) {
							alert("Please tick at least one child to enrol.");
							return false;
						}
					});
				</script>');
			}
		}
		
		/*
		* User enrolling self
		*/
		
		if ($mustBeInCohort && !cohort_is_member($mustBeInCohort->id, $USER->id)) {
        	// don't display the self-enrol section at all
        } else {

			$heading = 'Enrol yourself in this activity';
	        $mform->addElement('header', 'selfheader', $heading, array('class'=>'collapsed'));
			
			if (enrol_user_is_enrolled($USER->id, $instance->id)) { 
				
				//User is already enrolled
				$mform->addElement('html', $OUTPUT->successbox('You are already enrolled in this activity.<br/><a class="btn" href="/course/view.php?id='.$instance->courseid.'" style="font-size:0.9em; position:relative; top:5px;">Go to activity page</a>'));

	        } else {
	        
	        	//User is allowed to enrol
				$this->add_action_buttons(false, get_string('enrolme', 'enrol_self'), false);
					        
			}
		
		}
		/*
		* End user enrolling self
		*/

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $instance->courseid);

        $mform->addElement('hidden', 'instance');
        $mform->setType('instance', PARAM_INT);
        $mform->setDefault('instance', $instance->id);
    }

    public function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);
        $instance = $this->instance;

        if ($this->toomany) {
            $errors['notice'] = get_string('error');
            return $errors;
        }

        if ($instance->password) {
            if ($data['enrolpassword'] !== $instance->password) {
                if ($instance->customint1) {
                    $groups = $DB->get_records('groups', array('courseid'=>$instance->courseid), 'id ASC', 'id, enrolmentkey');
                    $found = false;
                    foreach ($groups as $group) {
                        if (empty($group->enrolmentkey)) {
                            continue;
                        }
                        if ($group->enrolmentkey === $data['enrolpassword']) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        // We can not hint because there are probably multiple passwords.
                        $errors['enrolpassword'] = get_string('passwordinvalid', 'enrol_self');
                    }

                } else {
                    $plugin = enrol_get_plugin('self');
                    if ($plugin->get_config('showhint')) {
                        $hint = textlib::substr($instance->password, 0, 1);
                        $errors['enrolpassword'] = get_string('passwordinvalidhint', 'enrol_self', $hint);
                    } else {
                        $errors['enrolpassword'] = get_string('passwordinvalid', 'enrol_self');
                    }
                }
            }
        }

        return $errors;
    }
}
