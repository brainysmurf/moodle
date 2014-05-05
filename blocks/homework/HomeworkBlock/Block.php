<?php

namespace SSIS\HomeworkBlock;

class Block
{
	public $today;
	public $display;

	public function __construct()
	{
		$this->today = date('Y-m-d');

		// Load the timetable stuff
		global $CFG;
		require $CFG->libdir . '/ssistimetable.php';

		require __DIR__ . '/Display.php';
		$this->display = new Display($this);

		require __DIR__ . '/HomeworkItem.php';
	}

	/**
	 * Viewing modes
	 */

	/**
	 * Returns the userID of the current user, or the user to view info for if in parent mode
	 */
	public function userID() {
		global $SESSION, $USER;

		if (!empty($SESSION->homeworkBlockUser)) {
			return $SESSION->homeworkBlockUser;
		}

		$mode = $this->mode();

		if ($mode == 'parent') {
			$children = $SESSION->usersChildren;
			$child = reset($children);
			$SESSION->homeworkBlockUser = $child->userid;
			return $child->userid;
		} else {
			return $USER->id;
		}
	}

	/**
	 * Returns the mode the current user is in
	 * (The default mode for the users role if the mode hasn't been switched)
	 */
	public function mode()
	{
		global $SESSION, $CFG;
		if (isset($SESSION->homeworkBlockMode)) {
			return $SESSION->homeworkBlockMode;
		}

		$possibleModes = $this->possibleModes();
		return $possibleModes[0];
	}

	/**
	 * Which modes can the current user switch to?
	 */
	public function possibleModes()
	{
		// The is_student() etc functions come from here:
		global $CFG;
		require_once $CFG->dirroot . '/local/dnet_common/sharedlib.php';

		if (\is_student()) {
			return array('student');
		} elseif (\is_parent()) {
			return array('parent');
		} elseif (\is_teacher()) {
			return array('teacher', 'pastoral');
			return 'teacher';
		} elseif (\is_admin()) {
			return array('teacher', 'pastoral');
		} elseif (\is_secretary()) {
			return array('teacher', 'pastoral');
		}

		// Shouldn't get to here, but just in case...
		return array('guest');
	}


	public function changeMode($newMode)
	{
		global $SESSION;

		$possibleModes = $this->possibleModes();
		if (in_array($newMode, $possibleModes)) {
			$SESSION->homeworkBlockMode = $newMode;
			return true;
		}
		return false;
	}



	/**
	 * Capability checks
	 */

	/**
	 * Is the logged in user allowed to add homework to a course?
	 */
	public function canAddHomework($courseid)
	{
		$mode = $this->mode();
		if ($mode == 'teacher' || $mode == 'student') {

			$context =\context_course::instance($courseid);
			return has_capability('block/homework:addhomework', $context);

			return true;
		}
		return false;
	}

	/**
	 * Is the logged in user allowed to approve (make visible) homework in a course?
	 */
	public function canApproveHomework($courseid)
	{
		$mode = $this->mode();
		if ($mode == 'teacher') {
			$context = \context_course::instance($courseid);
			return has_capability('block/homework:approvehomework', $context);
		}
		return false;
	}



	/**
	 * Loading user's classes and courses
	 */

	/**
	 * Get all classes (groups) a user is in
	 */
	public function getUsersGroups($userid)
	{
		$timetable = new \SSIS\Timetable($userid);
		$classes = $timetable->getClasses();
		return $classes;
	}

	/**
	 * Get only an array of class group IDs a user is in
	 */
	public function getUsersGroupIDs($userid)
	{
		$classes = $this->getUsersGroups($userid);
		$groups = array();
		foreach ($classes as $class) {
			$groups += $class['groups'];
		}
		return array_keys($groups);
	}



	/**
	 * Getting homework
	 * @param array groupIDs (optional) Limit to homework assigned in the given groupIDs
	 * @param array courseIDs (optional) Limit to homework assigned in the given courseIDs
	 * @param array assignedFor (optional) Limit to homework assigned to do on the given date(s) (Y-m-d format)
	 * @param bool approved	(optional)
	 *                      true for only approved,
	 *                      false for only not approved,
	 *                      null for everything
	 * @param bool distinct (optional)
	 *                      if true, will only return one row for each homework item.
	 *                      if false, will return a row for every day a homework item is set.
	 *                      default is true
	 * @param bool past (optional)
	 *                  true to only get items due in the past,
	 *                  false to only get items due in the future,
	 *                  null to get everything.
	 *                  If distinct is true, "past" means that the item's due date is in the past.
	 *                  If distinct is false, "past" means that the assigned day is in the past.
	 * @param string (Y-m-d)	dueDate	(optional) get only items due on a specific day (Y-m-d format)
	 */
	public function getHomework(
		$groupIDs = false,
		$courseIDs = false,
		$assignedFor = false,
		$approved = true,
		$distinct = true,
		$past = false,
		$dueDate = false,
		$order = 'days.date ASC, hw.approved ASC, hw.duedate ASC'
	) {
		global $DB;
		$params = array();

		// The purpose of the "key" field is because Moodle makes the first column
		// the key for the array it returns. So it needs to be unique to get all the rows
		// from the join

		$sql = 'SELECT ' . ($distinct ? 'DISTINCT' : 'CONCAT(hw.id, \'-\', days.id) AS key,') . '
			hw.*,
			days.date AS assigneddate,
			crs.fullname AS coursename,
			usr.username AS username,
			usr.firstname AS userfirstname,
			usr.lastname AS userlastname
		FROM {block_homework} hw
		JOIN {course} crs ON crs.id = hw.courseid
		JOIN {block_homework_assign_dates} days ON days.homeworkid = hw.id
		LEFT JOIN {user} usr ON usr.id = hw.userid';

		$where = false;

		// Group IDs
		if (is_array($groupIDs) && count($groupIDs) > 0) {
			$sql .= ($where ? ' AND' : ' WHERE');
			$where = true;

			if (count($groupIDs) == 1) {
				$sql .= ' hw.groupid = ?';
				$params[] = $groupIDs[0];
			} elseif (count($groupIDs)) {
				$sql .= ' hw.groupid IN (' . implode(',', $groupIDs) . ')';
			}
		}

		// Course IDs
		if (is_array($courseIDs) && count($courseIDs) > 0) {
			$sql .= ($where ? ' AND' : ' WHERE');
			$where = true;

			if (count($courseIDs) == 1) {
				$sql .= ' hw.courseid = ?';
				$params[] = $courseIDs[0];
			} elseif (count($courseIDs)) {
				$sql .= ' hw.courseid IN (' . implode(',', $courseIDs) . ')';
			}
		}

		// Assigned dates
		if (is_array($assignedFor)) {

			$sql .= ($where ? ' AND' : ' WHERE');
			$where = true;

			if (count($assignedFor) == 1) {
				$sql .= ' days.date = ?';
				$params[] = $assignedFor[0];
			} elseif (count($assignedFor)) {
				$sql .= ' days.date IN (\'' . implode('\', \'', $assignedFor) . '\')';
			}
		}

		// Approved?
		if (!is_null($approved)) {

			$sql .= ($where ? ' AND' : ' WHERE');
			$where = true;

			if ($approved) {
				$sql .= ' approved = 1';
			} else {
				$sql .= ' approved = 0';
			}
		}

		// In the past?
		if (!is_null($past)) {

			$sql .= ($where ? ' AND' : ' WHERE');
			$where = true;

			if ($distinct) {
				if ($past) {
					$sql .= ' hw.duedate < ?';
				} else {
					$sql .= ' hw.duedate >= ?';
				}
			} else {
				if ($past) {
					$sql .= ' days.date < ?';
				} else {
					$sql .= ' days.date >= ?';
				}
			}
			$params[] = $this->today;
		}


		if ($order) {
			$sql .= ' ORDER BY ' . $order;
		}

		$records = $DB->get_records_sql($sql, $params);
		$return = array();
		foreach ($records as $record) {
			$return[] = new HomeworkItem($record);
		}

		return $return;
	}
}
