<?php

namespace SSIS\ActivityCenter;

class ActivityCenter
{
	public $data;
	public $display;
	const PATH = '/local/dnet_activity_center/';

	function __construct()
	{
		require __DIR__ . '/Data.php';
		$this->data = new Data($this);

		require __DIR__ . '/Display.php';
		$this->display = new Display($this);
	}

	/**
	 * Returns the userID of the current user, or the user we are viewing information about
	 */
	public function userID() {
		global $SESSION, $USER;

		if (!empty($SESSION->activityCenterUserID)) {
			return $SESSION->activityCenterUserID;
		}

		return $USER->id;
	}

	public function possibleModes()
	{
		//TODO: Return array of possible modes here
	}

	/**
	 * Enrol a user as a manager to a course using the manual enrolment method
	 */
	public function addManager($courseID, $userID)
	{
		// Get the instance
		$instances = enrol_get_instances($courseID, 1);
		foreach ($instances as $possibleInstance) {
			if ($possibleInstance->enrol == 'manual') {
				// This is the one we want
				$instance = $possibleInstance;
				break;
			}
		}

		if (!isset($instance)) {
			throw new Exception("Unable to find a manual enrolment method for course {$courseID}");
		}

		$manualEnrolmentPlugin = enrol_get_plugin('manual');

		$manualEnrolmentPlugin->enrol_user($instance, $userID, Data::MANAGER_ROLE_ID);
		// ^ that doesn't return anything, so we have to assume it worked...
		return true;
	}
}
