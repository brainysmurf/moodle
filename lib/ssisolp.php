<?php

class OLPManager {

	const TEACHER_ROLE_ID = 3; // ID of the Teacher / Editor role
	const STUDENT_ROLE_ID = 5; // ID of the Student / Viewer role
	const TEACHER_COHORT_ID = 73; // ID of the teachersALL cohort

	const OLP_CATEGORY_ID = 115;

	private $colors; // CLI colors class

	function __construct()
	{
		global $CFG;

		require_once($CFG->dirroot. '/course/lib.php');
		require_once($CFG->dirroot. '/enrol/locallib.php');
		require_once($CFG->dirroot. '/cohort/lib.php');
		require_once($CFG->dirroot. '/enrol/cohort/locallib.php');

		require_once($CFG->dirroot. '/lib/clilib.php');
		$this->colors = new Colors();
	}

	/**
	 * Make sure all users who should have an OLP do have one
	 * and they are set up correctly
	 */
	public function run()
	{
		// Load users
		$users = $this->getStudentsWhoShouldHaveOLP();
		if (count($users) < 1) {
			throw new Exception("Unable to load users to check");
		}

		// Check each user
		foreach ($users as $user) {

			// Blank line between users to make output clearer
			echo "\n";

			/**
			 * Check user has a powerschool ID
			 */
			if (!$user->idnumber) {
				$this->output($user, 'User has no idnumber', 'ERROR');
				continue;
			}

			/**
			 * Ensure OLP course exists for user
			 */
			if ($olp = $this->getUsersOLP($user)) {
				$this->output($user, "OLP course exists: ID {$olp->id}", 'DEBUG');
			} else {
				$this->output($user, 'User has no OLP', 'NOTICE');

				// Create course if it doesn't exist
				if ($olp = $this->createOLP($user)) {
					$this->output($user, "Created OLP course: ID {$olp->id}", 'DEBUG');
				}
			}


			/**
			 * Ensure user is an editor in their OLP
			 */
			if ($this->isUserEditorInOLP($user, $olp)) {
				$this->output($user, 'User is enroled as editor in OLP', 'DEBUG');
			} else {
				$this->output($user, 'User was not enroled in OLP', 'NOTICE');

				// Enrol if not already
				if ($this->enrolStudentInOLP($user, $olp)) {
					$this->output($user, "Enroled in OLP", 'DEBUG');
				}
			}


			/**
			 * Ensure teachers are enroled as students (viewers) in the OLP
			 */
			if ($this->checkCohortEnrolmentExists($olp->id, self::STUDENT_ROLE_ID, self::TEACHER_COHORT_ID)) {

				//Enrolment already exists
				$this->output($user, 'Teacher cohort -> Student role enrolment exists in OLP', 'DEBUG');

			} else {

				$this->output($user, 'No Teacher cohort -> Student role enrolment in OLP', 'NOTICE');

				// Create enrolment
				if ($teacherStudentEnrolmentID = $this->createCohortEnrolment(
					$olp,
					self::STUDENT_ROLE_ID,
					self::TEACHER_COHORT_ID,
					'Cohort sync (All teachers as viewers)', //Name of enrolment method to create
					true //Disabled?
				)) {
					$this->output($user, "Created enrolment method {$teacherStudentEnrolmentID}", 'DEBUG');
				}

			}

			/**
			 * Ensure teachers are enroled as teachers (editors) in the OLP
			 */
			if ($this->checkCohortEnrolmentExists($olp->id, self::TEACHER_ROLE_ID, self::TEACHER_COHORT_ID)) {

				//Enrolment already exists
				$this->output($user, 'Teacher cohort -> Teacher role enrolment exists in OLP', 'DEBUG');

			} else {

				$this->output($user, 'No Teacher cohort -> Teacher role enrolment in OLP', 'NOTICE');

				// Create enrolment
				if ($teacherTeacherEnrolmentID = $this->createCohortEnrolment(
					$olp,
					self::TEACHER_ROLE_ID,
					self::TEACHER_COHORT_ID,
					'Cohort sync (All teachers as editors)', //Name of enrolment method to create
					false //Disabled?
				)) {
					$this->output($user, "Created enrolment method {$teacherTeacherEnrolmentID}", 'DEBUG');
				}

			}

		} // end foreach user

	}



	/**
	 * Returns all users in the database who should have an OLP course set up
	 */
	public function getStudentsWhoShouldHaveOLP()
	{
		global $DB;
		return $DB->get_records_sql('
		SELECT * FROM {user}
		WHERE email LIKE \'%@student.ssis-suzhou.net\'
		AND auth != \'nologin\'
		');
	}



	/**
	 * Return all the course data for a user's OLP
	 * If one exists
	 */
	public function getUsersOLP($user)
	{
		if (!$user->idnumber) {
			return false;
		}

		$courseIDNumber = 'OLP:' . $user->idnumber;

		//Load course from the DB
		global $DB;
		try {
			$course = $DB->get_record('course', array('idnumber' => $courseIDNumber), '*', MUST_EXIST);
			return $course;
		} catch (Exception $e) {
			return false;
		}
	}


	/**
	 * Create an OLP course for the given user
	 * @param  stdClass $user User to create the course for (user object)
	 * @throws Exception
	 */
	public function createOLP($user)
	{
		$idnumber = "OLP:{$user->idnumber}";

		$courseData = new \stdClass();
		$courseData->fullname = $user->lastname . ', ' . $user->firstname;
		$courseData->shortname = $idnumber;
		$courseData->idnumber = $idnumber;
		$courseData->format = 'onetopic';
		$courseData->category = self::OLP_CATEGORY_ID;

		$course = create_course($courseData);

		if (!$course) {
			throw new Exception("Unable to create course " . print_r($courseData, true));
		}

		return $course;
	}



	/**
	 * Returns true or false if the given student (user)
	 * has editing capabilities in the given course
	 */
	public function isUserEditorInOLP($user, $course)
	{
		$courseContext = context_course::instance($course->id);

		// They should have this capability if they're correctly enrolled
		$checkCapability = 'moodle/course:manageactivities';

		return is_enrolled($courseContext, $user, $checkCapability, true);
	}



	/**
	 * Return the given user as an editor (Teacher) in the given OLP course
	 * @return int 1 if the user was already enroled
	 * @return int 2 if the user has been enroled now
	 * @return int false if the user is not enrolled
	 */
	public function enrolStudentInOLP($user, $course)
	{
		// Get enrolment instance to enrol them via
		$enrolmentInstance = $this->getManualEnrolmentInstanceForCourse($course);

		if (!$enrolmentInstance) {
			throw new \Exception("Manual enrolment instance doesn't exist for course {$course->id}");
		}

		// How long to enrol for
		// (Forever)
		$today = time();
		$timestart = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);
		$timeend = 0;

		// Load the manual enrolment plugin
		$plugin = enrol_get_plugin('manual');

		// Enrol as a teacher using the manual enrolment plugin
		$success = $plugin->enrol_user($enrolmentInstance, $user->id, self::TEACHER_ROLE_ID, $timestart, $timeend);

		if ($this->isUserEditorInOLP($user, $course)) {
			return true;
		} else {
			throw new \Exception('Unable to enrol student in course');
		}
	}



	/**
	 * Returns the manual enrolment instance for a course
	 */
	private function getManualEnrolmentInstanceForCourse($course)
	{
		global $DB;
		try {
			$enrolmentInstance = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $course->id));
			return $enrolmentInstance;
		} catch (Exception $e) {
			return false;
		}
	}



	/**
	 * Check a cohort sync enrolment method exists in  course
	 * with the given roleID and cohortID
	 */
	public function checkCohortEnrolmentExists($courseID, $roleID, $cohortID)
	{
		global $DB;
		return $DB->count_records('enrol', array(
			'enrol' => 'cohort',
			'courseid' => $courseID,
			'roleid' => $roleID,
			'customint1' => $cohortID
		));
	}



	/**
	 * Add a cohort sync enrolment method in a course
	 */
	public function createCohortEnrolment($course, $roleID, $cohortID, $name, $disabled = false)
	{
		//Load the cohort sync enrolment plugin
		$plugin = enrol_get_plugin('cohort');

		$status = $disabled ? ENROL_INSTANCE_DISABLED : ENROL_INSTANCE_ENABLED;

		//Enrol teachers as students (disabled by default)
		$enrolmentMethodID = $plugin->add_instance(
			$course,
			array(
				'name' => $name,
				'customint1' => $cohortID,
				'roleid' => $roleID,
				'status' => $status
			)
		);

		// Run the cohort sync function so new users become enroled
		$trace = new null_progress_trace();
		// TODO: This is slow. Run it at the end or something. It'll get run by a cron eventually anyway
		#enrol_cohort_sync($trace, $course->id);

		return $enrolmentMethodID;
	}



	/**
	 * Write debugging output
	 */
	private function output($user, $string, $type = 'INFO')
	{
		$line = str_pad("[{$user->username}]", 30);
		$line .= $string;

		switch ($type) {

			case 'ERROR':
				$foreground = 'red';
				break;

			case 'NOTICE':
				$foreground = 'cyan';
				break;

			case 'DEBUG':
				$foreground = 'green';
				break;

			case 'INFO':
			default:
				$foreground = 'white';
		}

		echo $this->colors->getColoredString($line, $foreground, null) . "\n";
	}
}
