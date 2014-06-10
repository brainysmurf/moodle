<?php

namespace SSIS\ActivityCenter;

class ActivityCenter
{
	public $data;
	public $display;
	const PATH = '/local/dnet_activity_center/';
	public $activityCourseCategory = 1;

	function __construct()
	{
		require __DIR__ . '/Data.php';
		$this->data = new Data($this);

		require __DIR__ . '/Display.php';
		$this->display = new Display($this);
	}

	public function getPath()
	{
		return self::PATH;
	}

	/**
	 * Returns the userID of the current user, or the user we are viewing information about
	 */
	public function getUserID() {
		global $SESSION, $USER;

		if (!empty($SESSION->activityCenterUserID)) {
			return $SESSION->activityCenterUserID;
		}

		return $USER->id;
	}



	/**
	 * Modes
	 * (admin / teacher / student)
	 */

	public function setCurrentMode($mode)
	{
		global $SESSION;

		$possibleModes = $this->getPossibleModes();
		if (!in_array($mode, $possibleModes)) {
			return false;
		}

		// Backward compatability
		if ($mode == 'teacher') {
			$SESSION->dnet_activity_center_submode = 'becometeacher';
		} else {
			$SESSION->dnet_activity_center_submode = '';
		}

		$SESSION->activityCenterMode = $mode;

		return true;
	}

	public function getCurrentMode()
	{
		global $SESSION;

		if (isset($SESSION->activityCenterMode)) {
			return $SESSION->activityCenterMode;
		}

		$possibleModes = $this->getPossibleModes();
		return $possibleModes[0];
	}

	/**
	 * Returns an array of the modes the current user is allowed to use
	 */
	public function getPossibleModes()
	{
		global $SESSION, $USER;

		require_once dirname(dirname(__DIR__)) . '/dnet_common/sharedlib.php';

		if (is_admin()) {
			return array('admin', 'teacher');
		} elseif (is_teacher()) {
			return array('teacher');
		} elseif (is_student()) {
			return array('student');
		}

		return array();
	}

	/**
	 * Returns the name of the first tab in the given mode
	 */
	public function defaultViewForMode($mode)
	{
		$tabs = $this->display->tabs[$mode];
		reset($tabs);
		return key($tabs);
	}

	public function isValidView($view)
	{
		$mode = $this->getCurrentMode();
		$view = preg_replace("/[^a-zA-Z0-9]+/", '', $view);
		$file = dirname(__DIR__) . '/views/' . $mode . '/' . $view . '.php';
		return file_exists($file);
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

	/**
	 * De-enrol a user as a manager to a course using the manual enrolment method
	 */
	public function removeManager($courseID, $userID)
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

		$manualEnrolmentPlugin->unenrol_user($instance, $userID, Data::MANAGER_ROLE_ID);
		// ^ that doesn't return anything, so we have to assume it worked...
		return true;
	}
}
