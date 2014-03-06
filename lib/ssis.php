<?php

/**
 * Contains a bunch of static functions related to
 * SSIS tweaks
 */
class SSIS
{

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
