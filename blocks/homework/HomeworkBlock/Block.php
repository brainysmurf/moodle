<?php

namespace SSIS\HomeworkBlock;

class Block
{
	public $today;
	public $display;
	public $userID;

	public function __construct()
	{
		$this->today = date('Y-m-d');

		// Load the timetable stuff
		global $CFG;
		require $CFG->libdir . '/ssistimetable.php';

		require __DIR__ . '/Display.php';
		$this->display = new Display($this);

		require __DIR__ . '/HomeworkItem.php';
		require __DIR__ . '/HomeworkStats.php';
	}

	/**
	 * Viewing modes...
	 */

	/**
	 * Returns the userID of the current user, or the user to view info for if in parent mode
	 */
	public function userID()
	{
		if ($this->userID) {
			return $this->userID;
		}

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

	public function generateFeedKey($user)
	{
		global $DB;

		$key = sha1($user->id . $user->username . $user->firstaccess . $user->timecreated);

		return $key;
	}

	public function generateFeedURL($userID = false)
	{
		global $DB, $CFG;

		if ($userID === false) {
			$userID = $this->userID();
		}
		$user = $DB->get_record('user', array('id' => $userID));

		$key = $this->generateFeedKey($user);

		$url = $CFG->wwwroot . '/blocks/homework/feed/?';

		$query = array(
			'u' => $user->username,
			'k' => $key
		);

		$url .= http_build_query($query);

		return $url;
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
		$SESSION->homeworkBlockMode = $possibleModes[0];
		return $possibleModes[0];
	}

	/**
	 * Which modes can the current user switch to?
	 */
	public function possibleModes()
	{
		global $CFG, $SESSION;
		// The is_student() etc functions come from this file:
		require_once $CFG->dirroot . '/local/dnet_common/sharedlib.php';

		if ($SESSION->userIsTeacher) {
			return array('teacher', 'pastoral');
		} elseif ($SESSION->userIsStudent) {
			return array('student');
		} elseif ($SESSION->userIsParent) {
			return array('parent');
		} elseif ($SESSION->userIsSecretary) {
			return array('pastoral');
		} elseif (\is_admin()) {
			return array('pastoral');
		}

		// Shouldn't get to here, but just in case...
		return array('pastoral');
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

	public function canEditHomeworkItem($homeworkItem)
	{
		if ($homeworkItem->private) {
			return $homeworkItem->userid == $this->userID();
		} else {
			return $this->canApproveHomework($homeworkItem->courseid);
		}
	}

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
	public function getUsersGroups($userid, $activeOnly = true)
	{
		$timetable = new \SSIS\Timetable($userid);

		if ($this->mode() == 'teacher') {
			$classes = $timetable->getTeacherClasses($activeOnly);
		} else {
			$classes = $timetable->getStudentClasses($activeOnly);
		}

		return $classes;
	}

	/**
	 * Get only an array of class group IDs a user is in
	 */
	public function getUsersGroupIDs($userid, $activeOnly = true)
	{
		$classes = $this->getUsersGroups($userid, $activeOnly);
		return $this->extractGroupIDsFromTimetable($classes);
	}

	public function extractGroupIDsFromTimetable($classes)
	{
		$groups = array();
		foreach ($classes as $classID => $class) {
			$groups += $class['groups'];
		}
		return array_keys($groups);
	}

	/**
	 * Return every group (class) in the school
	 */
	public function getAllGroups($grade = null)
	{
		$timetable = new \SSIS\Timetable();
		return $timetable->getAllClasses(true, $grade);
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
	 * @param string (Y-m-d)	assignedRangeStart	(optional) Get items assigned for this date and on
	 * @param string (Y-m-d)	assignedRangeEnd	(optional) Get items assigned for this date or before
	 */
	public function getHomework(
		$groupIDs = false,
		$courseIDs = false,
		$assignedFor = false,
		$approved = true,
		$distinct = true,
		$past = false,
		$dueDate = false,
		$order = null,
		$assignedRangeStart = null,
		$assignedRangeEnd = null,
		$includePrivate = true
	) {
		global $DB;
		$params = array();

		// The purpose of the "key" field is because Moodle makes the first column
		// the key for the array it returns. So it needs to be unique to get all the rows
		// from the join

		$sql = 'SELECT ' . ($distinct ? 'DISTINCT' : 'CONCAT(hw.id, \'-\', days.id) AS key,') . '
			hw.*,
			days.date AS assigneddate,
			crs.id AS courseid,
			crs.fullname AS coursename,
			usr.username AS username,
			usr.firstname AS userfirstname,
			usr.lastname AS userlastname
		FROM {block_homework} hw
		LEFT JOIN {course} crs ON crs.id = hw.courseid
		JOIN {block_homework_assign_dates} days ON days.homeworkid = hw.id
		LEFT JOIN {user} usr ON usr.id = hw.userid';

		$sql .= ' WHERE (';
		$where = true;

		// Begin selecting portion...
		$and = false;

		$privateSelector = "(private = 1 AND userID = " . intval($this->userID()) . ')';

		if ($includePrivate) {
			// Include private homework for the current logged in user
			$sql .= ' ' . $privateSelector;
			$and = true;
		} else {
			// Exclude all private homework
			$sql .= ' private = 0';
			$and = true;
		}

		if (is_array($groupIDs)) {

			if (count($groupIDs) < 1) {
				return array();
			}

			// Group IDs
			$sql .= ($and ? ' OR' : ' ');
			$and = true;

			if (count($groupIDs) == 1) {
				$sql .= ' hw.groupid = ?';
				$params[] = $groupIDs[0];
			} elseif (count($groupIDs)) {
				$sql .= ' hw.groupid IN (' . implode(',', $groupIDs) . ')';
			}
		}

		// Course IDs
		if (is_array($courseIDs)) {

			if (count($courseIDs) < 1) {
				return array();
			}

			$sql .= ($and ? ' OR' : ' ');
			$and = true;

			if (count($courseIDs) == 1) {
				$sql .= ' hw.courseid = ?';
				$params[] = $courseIDs[0];
			} elseif (count($courseIDs)) {
				$sql .= ' hw.courseid IN (' . implode(',', $courseIDs) . ')';
			}
		}

		$sql .= ')';
		// End selecting portion...

		// Begin filtering portion...

		// Show only stuff that has a start date (visible date) of today or earlier
		if ($this->mode() != 'teacher' && $this->mode() != 'pastoral') {
			$sql .= ($where ? ' AND' : ' WHERE');
			$where = true;

			$sql .= ' (hw.startdate <= ? OR ' . $privateSelector . ')';
			$params[] = $this->today;
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

			// The IS NULL part is so private homework remains included
			if ($approved) {
				$sql .= ' (approved = 1 OR ' . $privateSelector . ')';
			} else {
				$sql .= ' (approved = 0 OR ' . $privateSelector . ')';
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


		if (!is_null($assignedRangeStart)) {
			$sql .= ($where ? ' AND' : ' WHERE');
			$where = true;
			$sql .= ' days.date >= ?';
			$params[] = $assignedRangeStart;
		}

		if (!is_null($assignedRangeEnd)) {
			$sql .= ($where ? ' AND' : ' WHERE');
			$where = true;
			$sql .= ' days.date <= ?';
			$params[] = $assignedRangeEnd;
		}

		if (is_null($order)) {
			$order = 'days.date ASC, hw.approved ASC, hw.duedate ASC';
		}

		if ($order) {
			$sql .= ' ORDER BY ' . $order;
		}

		#print_object($sql);
		#print_object($params);

		$records = $DB->get_records_sql($sql, $params);
		$return = array();
		foreach ($records as $record) {
			$return[] = new HomeworkItem($record);
		}

		return $return;
	}


	/**
	 * Getting courses a user is in...
	 */

	/**
	 * Returns every teaching and learning course
	 */
	public function getAllCourses()
	{
		global $DB;
		$sql = 'SELECT
			crs.id,
			crs.fullname
		FROM {course} crs
		LEFT JOIN {context} ct ON ct.instanceid = crs.id AND ct.contextlevel = 50
		WHERE
			ct.path LIKE \'/1/6156/%\'
			';

		$sql .= 'ORDER BY crs.fullname';
		$values = array();
		return $DB->get_records_sql($sql, $values);
	}

	/**
	 * Returns all courses in the Teaching & Learning category the user is enrolled in
	 * Not actually used now. It looks up the classes from the timetable profile field instead
	 */
	public function getUsersCourses($userid, $roleid = null)
	{
		global $DB;

		$values = array(
			$userid
		);

		$sql = 'SELECT
			crs.id,
			crs.fullname
		FROM {role_assignments} ra
		JOIN {context} ct ON ct.id = ra.contextid
		JOIN {course} crs ON crs.id = ct.instanceid
		WHERE
			ra.userid = ?
			AND
			ct.path LIKE \'/1/6156/%\'
			';

		if (!is_null($roleid)) {
			$sql .= ' AND ra.roleid = ? ';
			$values[] = $roleid;
		}

		$sql .= 'ORDER BY crs.fullname';

		return $DB->get_records_sql($sql, $values);
	}

	public function coursesToIDs($courses)
	{
		$ids = array();
		foreach ($courses as $course) {
			$ids[] = intval($course->id);
		}
		return $ids;
	}

	/**
	 * Returns all course IDs that the user is enrolled in
	 */
	public function getUsersCourseIDs($userid, $roleid = null)
	{
		$courses = $this->getUsersCourses($userid, $roleid);
		return $this->coursesToIDs($courses);
	}

	public function getUsersTaughtCourses($userid)
	{
		return $this->getUsersCourses($userid, 3);
	}

	public function getUsersTaughtCourseIDs($userid)
	{
		return $this->getUsersCourseIDs($userid, 3);
	}


/**
 * select distinct(usr.*)
* from ssismdl_groups_members grpmbr
* join ssismdl_user usr on usr.id = grpmbr.userid
* join ssismdl_groups grp on grp.id = grpmbr.groupid
* join ssismdl_context ctx on ctx.instanceid = grp.courseid and ctx.contextlevel = 50
* join ssismdl_role_assignments ra on ra.contextid = ctx.id
* where groupid = 789
 */
	/*public function getTeacherForGroupID($groupID)
	{
		global $DB;



		$teachers = $DB->get_records_sql($sql, $params);
	}*/

	/**
	 * Email alerts
	 */
	/*public function emailTeacherOnNewHomework($homeworkItem, $from)
	{
		$subject = "Homework submitted for " . $homeworkItem->coursename;
		$teachers = $this->getTeacherForGroupID($homeworkItem->groupid);

		$messagehtml = 'Hello!';

		$messagetext = strip_tags($messagehtml);

		foreach ($teachers as $teacher) {

			email_to_user($user, $from, $subject, $messagetext, $messagehtml);

		}
	}*/
}
