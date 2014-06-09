<?php

namespace ClassroomTechTools\AppTemplate;

abstract class App
{
	// Subclasses should set these properties
	protected $appName; // Name of the app
	protected $path; // Path the app (releative to the moodle installation directory / url)

	public $data; // Data manager instance
	public $output; // Display manager instance
	public $sessionData; // Reference to this app's data in the Moodle session

	function __construct(DataManger $data, OutputManager $output)
	{
		#require __DIR__ . '/Data.php';
		#$this->data = new DataManager($this);

		#require __DIR__ . '/Output.php';
		#$this->output = new OutputManager($this);

		$this->data = $data;
		$this->output = $output;

		// Setup some session storage for the app
		global $SESSION;
		if (!isset($SESSION->{'app_' . $this->appName})) {
			$SESSION->{'app_' . $this->appName} = new \stdClass();
		}
		$this->sessionData = $SESSION->{'app_' . $this->appName};
	}

	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Returns the userID of the current user, or the user we are viewing information about
	 */
	public function getUserID() {

		if (!empty($this->sessionData->userID)) {
			return $this->sessionData->userID;
		}

		global $USER;
		return $USER->id;
	}

	/**
	 * Modes
	 * (admin / teacher / student etc.)
	 */
	public function setCurrentMode($mode)
	{
		$possibleModes = $this->getPossibleModes();
		if (!in_array($mode, $possibleModes)) {
			return false;
		}

		$this->sessionData->mode = $mode;
		return true;
	}

	/**
	 * Change the current mode the user is in and remember it for the duration of the session
	 */
	public function getCurrentMode()
	{
		global $SESSION;

		if (!empty($this->sessionData->mode)) {
			return $this->sessionData->mode;
		}

		$possibleModes = $this->getPossibleModes();
		return $possibleModes[0];
	}

	/**
	 * Returns an array of the modes the current user is allowed to use
	 */
	public abstract function getPossibleModes();
}
