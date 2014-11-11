<?php

/**
 * Class for managing Activity data: getting, updating, etc.
 */

namespace SSIS\ActivityCenter;

class Data
{
	const MANAGER_ROLE_ID = 1;
	const STUDENT_ROLE_ID = 5;
	private $activityCenter;

	public function __construct(ActivityCenter $activityCenter = null)
	{
		$this->activityCenter = $activityCenter;
	}

	public function getUserPDSelection($userid = false, $decode = false)
	{
		if (!$userid) {
			return false;
		}
		global $DB;
		$field = $DB->get_record('user_info_field', array('shortname' => 'pdchoices20145'), '*', MUST_EXIST);
		$info = $DB->get_record('user_info_data', array('userid' => $userid, 'fieldid' => $field->id));

		if ($decode) {
			if (empty($info)) {
				return false;
			}
			$info = $info->data;
			return json_decode($info);
		}

		return $info;
	}

	public function getUserGoal($userid = false)
	{
		if (!$userid) {
			return false;
		}
		global $DB;
		$field = $this->getPDGoalField();
		$info = $DB->get_record('user_info_data', array('userid' => $userid, 'fieldid' => $field->id));
		return $info;
	}

	/*
	Returns list of stuff
	*/
	public function getUsersSummary($cohort_name) {
		$choiceField = $this->getPDChoiceField();
		$goalField = $this->getPDGoalField();

		global $DB;
		$cohort = $DB->get_record('cohort', array('idnumber'=>$cohort_name));
		$sql = '
select
    usr.id,
	usr.username,
	usr.firstname,
	usr.lastname,
	(select infodata.data from {user_info_data} infodata where infodata.userid = usr.id and infodata.fieldid = ' . $goalField->id . ') as goals,
	(select infodata.data from {user_info_data} infodata where infodata.userid = usr.id and infodata.fieldid = ' . $choiceField->id . ') as pd,
	string_agg(distinct(crs.fullname), \'|\') as activities
from
	{user_enrolments} ue
join {user} usr on usr.id = ue.userid
join {enrol} enrol on enrol.id = ue.enrolid
join {course} crs on crs.id = enrol.courseid
join {context} ctx on (ctx.instanceid = enrol.courseid and ctx.contextlevel = 50) --contextlevel 50 = a course
join {role_assignments} ra on ra.contextid = ctx.id and ra.userid = usr.id
join {cohort_members} chrt_mmbrs on usr.id = chrt_mmbrs.userid
where
	chrt_mmbrs.cohortid = '.$cohort->id.'
	and
	ctx.path like \'/1/3/%\' --contextid of activities category
	and
	ra.roleid = ' . self::MANAGER_ROLE_ID . ' --manager roleid
	and
	enrol.enrol = \'manual\' --exclude cohort sync managers
group by usr.id
order by usr.firstname
';

		$rows = $DB->get_records_sql($sql, array($cohort->id));
		return $rows;
	}

	/**
	 * Returns the goals entered by every user
	 */
	public function getAllUsersGoals()
	{
		global $DB;

		$choiceField = $this->getPDChoiceField();
		$goalField = $this->getPDGoalField();

		$sql = 'select
usr.username,
usr.firstname,
usr.lastname,
(select infodata.data from {user_info_data} infodata where infodata.userid = usr.id and infodata.fieldid = ' . $goalField->id . ') as goals,
(select infodata.data from {user_info_data} infodata where infodata.userid = usr.id and infodata.fieldid = ' . $choiceField->id . ') as pd
from {user} usr
where usr.email like \'%@ssis-suzhou.net\'
order by goals desc';

		$rows = $DB->get_records_sql($sql);
		return $rows;
	}

	private function getPDChoiceField()
	{
		global $DB;
		return $DB->get_record('user_info_field', array('shortname' => 'pdchoices20145'), '*', MUST_EXIST);
	}

	/**
	 * Returns the custom profile field that contains the user's PD goals
	 */
	private function getPDGoalField()
	{
		global $DB;
		return $DB->get_record('user_info_field', array('shortname' => 'goal20145'), '*', MUST_EXIST);
	}

	/**
	 * Return all pd the given user ID is signed up for
	 */
	public function getPDSignedUp($courseID = false, $userID = false)
	{
		global $DB;

		$sql = "SELECT
			CONCAT(crs.id, '-', usr.id), -- unique id so moodle keeps all the results
			usr.id as userid,
			usr.username,
			usr.firstname,
			usr.lastname,
			crs.id as id,
			crs.fullname,
			crs.visible
		from {user_enrolments} ue
		join {user} usr on usr.id = ue.userid
		join {enrol} enrol on enrol.id = ue.enrolid
		join {course} crs on crs.id = enrol.courseid
		join {context} ctx on (ctx.instanceid = enrol.courseid and ctx.contextlevel = 50) --contextlevel 50 = a course
		join {role_assignments} ra on ra.contextid = ctx.id and ra.userid = usr.id
		where
			ctx.path like '/1/128/%' --contextid of activities category
			and
			ra.roleid = " . self::STUDENT_ROLE_ID . " --student roleid
			and
			enrol.enrol = 'manual' --exclude cohort sync managers
		";

		$params = array();

		if ($courseID) {
			$sql .= ' AND crs.id = ?';
			$params[] = $courseID;
		}

		if ($userID) {
			$sql .= ' AND usr.id = ?';
			$params[] = $userID;
		}

		return $DB->get_records_sql($sql, $params);
	}

	/**
	 * Return all activities the given user ID is supervising
	 */
	public function getActivitiesManaged($courseID = false, $userID = false)
	{
		global $DB;

		$sql = "SELECT
			CONCAT(crs.id, '-', usr.id), -- unique id so moodle keeps all the results
			usr.id as userid,
			usr.username,
			usr.firstname,
			usr.lastname,
			crs.id as id,
			crs.fullname,
			crs.visible
		from {user_enrolments} ue
		join {user} usr on usr.id = ue.userid
		join {enrol} enrol on enrol.id = ue.enrolid
		join {course} crs on crs.id = enrol.courseid
		join {context} ctx on (ctx.instanceid = enrol.courseid and ctx.contextlevel = 50) --contextlevel 50 = a course
		join {role_assignments} ra on ra.contextid = ctx.id and ra.userid = usr.id
		where
			ctx.path like '/1/3/%' --contextid of activities category
			and
			ra.roleid = " . self::MANAGER_ROLE_ID . " --manager roleid
			and
			enrol.enrol = 'manual' --exclude cohort sync managers
		";

		$params = array();

		if ($courseID) {
			$sql .= ' AND crs.id = ?';
			$params[] = $courseID;
		}

		if ($userID) {
			$sql .= ' AND usr.id = ?';
			$params[] = $userID;
		}

		return $DB->get_records_sql($sql, $params);
	}


	public function getManualEnrolmentMethodForActivity($courseID)
	{
		global $DB;
		return $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $courseID));
	}

	/**
	 * Returns an array of activity courses
	 * @param search Search for activities with this string in the name
	 * @param userID Return activities the given user ID is enroled as a Manager in
	 */
	public function getActivities($search = false, $userID = false, $path = '/1/%')
	{
		global $DB;

		$sql = "SELECT
			crs.fullname,
			crs.id,
			crs.summary,
			crs.visible
		FROM
			{course} crs
		JOIN
			{course_categories} cat ON cat.id = crs.category
		WHERE
			cat.path like ?";

		$params = array();
		$params[] = $path;

		if ($search) {
			$sql .= " AND REPLACE(LOWER(fullname), ' ', '') LIKE ?";
			$params[] = '%' . $term . '%';
		}

		$activities = $DB->get_records_sql($sql, $params);

		return $activities;
	}


	/**
	 * Returns an array of activity courses
	 * @param search Search for activities with this string in the name
	 * @param userID Return activities the given user ID is enroled as a Manager in
	 */
	public function getPDs($search = false, $userID = false)
	{
		global $DB;

		$sql = "SELECT
			crs.fullname,
			crs.id,
			crs.summary,
			crs.visible
		FROM
			{course} crs
		JOIN
			{course_categories} cat ON cat.id = crs.category
		WHERE
			cat.path like ?";

		$params = array();
		$params[] = "/1/123%";

		if ($search) {
			$sql .= " AND REPLACE(LOWER(fullname), ' ', '') LIKE ?";
			$params[] = '%' . $term . '%';
		}

		$activities = $DB->get_records_sql($sql, $params);

		return $activities;
	}


	/**
	 * Returns the user(s) enrolled as Manager in the given courseID
	 * (But not the cohort sync ones!)
	 */
	public function getActivityLeaders($courseID)
	{
		global $DB;

		$context = \context_course::instance($courseID);
		$users = get_enrolled_users($context, 'enrol/manual:enrol');

		print_object($users);
	}

	/**
	 * @param course Object of course data
	 */
	public function addSelfEnrolmentToActivityCourse($course, $maxEnrolledUsers = 0, $parentsCanEnrol = 1)
	{
		//Load the cohort sync enrolment plugin
		$plugin = enrol_get_plugin('self');

		//Enrol teachers as students (enabled by default)
		return $plugin->add_instance(
			$course,
			array(
				'name' => 'Self Enrolment (Student)',
				'roleid' => self::STUDENT_ROLE_ID,
				'status' => ENROL_INSTANCE_ENABLED,
				'password' => '',
				'customint1' => 1,
				'customint2' => 0,
				'customint3' => $maxEnrolledUsers,
				'customint4' => 0,
				'customtext1' => '',
				'customint5' => 0,
				'customint6' => 1,
				'customint7' => 0,
				'customint8' => $parentsCanEnrol
			)
		);
	}


	public function getActivitiesHeadCohortID()
	{
		global $CFG;
		require_once $CFG->dirroot . '/cohort/lib.php';
		return cohort_get_id('activitiesHEAD');
	}

	public function addActivitiesHeadCohortSync($course)
	{
		//Load the cohort sync enrolment plugin
		$plugin = enrol_get_plugin('cohort');

		return $plugin->add_instance(
			$course,
			array(
				'name' => 'Cohort Sync (Activity Heads -> Manager)',
				'roleid' => self::MANAGER_ROLE_ID,
				'status' => ENROL_INSTANCE_ENABLED,
				'password' => '',
				'customint1' => $this->getActivitiesHeadCohortID(), //Cohort ID
				'customint2' => 0,
				'customint3' => '',
				'customint4' => '',
				'customtext1' => '',
				'customint5' => '',
				'customint6' => '',
				'customint7' => '',
				'customint8' => ''
			)
		);
	}

	/**
	 * Checks if a course already has the activitiesHEAD cohort sync enrolment method
	 */
	public function doesCourseHaveActivitesHeadSync($course)
	{
		global $DB;
		$count = $DB->count_records('enrol', array(
			'enrol' => 'cohort',
			'courseid' => $course->id,
			'customint1' => $this->getActivitiesHeadCohortID()
		));
		return $count > 0;
	}
}
