<?php

/**
 * Contains a bunch of static functions related to SSIS tweaks
 */
class SSIS
{

	/**
	 * Set userIsParent, userIsTeacher, etc. variables in the session
	 * for easy use throughout the site
	 */
	public static function addUserInfoToSession(&$session, $user)
	{
		global $CFG;
		require_once($CFG->dirroot .'/cohort/lib.php');

		//These are handy to know throughout the site, but aren't used for frontpage redirects
		$session->userIsSiteAdmin = has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));
		$session->userIsTeacher = cohort_is_member_by_idnumber('teachersALL', $user->id);
		$session->userIsStudent = cohort_is_member_by_idnumber('studentsALL', $user->id);
		$session->userIsSecStudent = cohort_is_member_by_idnumber('studentsSEC', $user->id);
		$session->userIsSecretary = cohort_is_member_by_idnumber('secretariesALL', $user->id);

		/**
		 * Decide which frontpage the user should be redirected to when they visit /index.php
		 * (The redirection happens on /index.php:75)
		 * FYI SSIS users should be enrolled into at least one cohort, the syncing software sees to that
		 * TODO: What about users that are both parents and teachers?
		 */

		// Default is no redirection
		$session->frontpageSection = false;

		if ($session->userIsElemStudent = cohort_is_member_by_idnumber('studentsELEM', $user->id)) {
			$session->frontpageSection = 10;
		}

		if ($session->userIsParent = cohort_is_member_by_idnumber('parentsALL', $user->id)) {
			$session->frontpageSection = 6;

			//Cache user's children in the session
			$session->usersChildren = get_users_children($user->id);
		} else {
			$session->usersChildren = array();
		}

		if ($session->userIsHSStudent = cohort_is_member_by_idnumber('studentsHS', $user->id)) {
			$session->frontpageSection = 2;
		}

		if ($session->userIsMSStudent = cohort_is_member_by_idnumber('studentsMS', $user->id)) {
			$session->frontpageSection = 3;
		}

		if ($session->userIsSecTeacher = cohort_is_member_by_idnumber('teachersSEC', $user->id)) {
			$session->frontpageSection = 5;
		}

		if ($session->userIsElemTeacher = cohort_is_member_by_idnumber('teachersELEM', $user->id)) {
			$session->frontpageSection = 4;
		}

		if ($session->userIsSupStaff = cohort_is_member_by_idnumber('supportstaffALL', $user->id)) {
			$session->frontpageSection = 8;
		}

		if ($session->userIsSSISAdmin = cohort_is_member_by_idnumber('adminALL', $user->id)) {
			$session->frontpageSection = 7;
		}

		if (cohort_is_member_by_idnumber('teachersNEW', $user->id)) {
			$session->frontpageSection =11;
		}
	}

	/**
	 * Sets the users "password2" which is used by pam_pgsql to authenticate
	 * with moodle users on the mail server
	 */
	public static function update_user_password2($userid, $clearpassword)
	{
		global $DB, $CFG;
		$hashedpassword = md5($clearpassword . $CFG->passwordsaltmain);
		$DB->set_field('user', 'password2', $hashedpassword, array('id' => $userid));
	}

	/**
	 * Activity busses
	 */
	public static function getUserActivityBusRequirement($userid)
	{
		global $DB;
		try {
			$bus = $DB->get_field('user_activity_bus', 'bus', array('userid' => $userid), MUST_EXIST);
			return $bus ? true : false;
		} catch (Exception $e) {
			return null;
		}

	}

	public static function setUserActivityBusRequirement($userid, $bus = true)
	{
		global $DB;

		$bus = $bus ? 1 : 0;

		if (self::getUserActivityBusRequirement($userid) !== null) {

			// Already set - UPDATE
			return $DB->set_field('user_activity_bus', 'bus', $bus, array('userid' => $userid));

		} else {

			// Not already set - INSERT
			$row = new stdClass;
			$row->userid = $userid;
			$row->bus = $bus;
			return $DB->insert_record('user_activity_bus', $row);
		}
	}

}
