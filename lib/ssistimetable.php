<?php

/**
* Timetable functions,
* from the timetable stored in the custom profile field
*/

namespace SSIS;

class Timetable
{
	private static $timetableProfileFieldID = 18;

	private $userid;
	private $timetable;

	public function __construct($userid)
	{
		$this->userid = $userid;

		// Load the json from the db and parse it
		$this->timetable = $this->load();
	}

	private function load()
	{
		global $DB;
		$field = $DB->get_field('user_info_data', 'data', array(
			'userid' => $this->userid,
			'fieldid' => self::$timetableProfileFieldID
		));
		$timetable = json_decode($field);
		return $timetable;
	}

	/**
	 * Parse the 'enrollments' item of the user's json timetable into useful
	 * information, e.g. the course and group ids and teacher names
	 * @return  array of nice enrollment info
	 */
	public function getClasses()
	{
		global $DB;

		$enrollments = $this->timetable->enrollments;

		// Array of data to be returned
		$classes = array();

		// Cache of course data
		$courses = array();

		foreach ($enrollments as $courseIDNumber => $groups) {

			if (isset($courses[$courseIDNumber])) {
				// Use the cached course
				$course = $courses[$courseIDNumber];
			} else {
				// Load the course info
				$course = $DB->get_record('course', array('idnumber' => $courseIDNumber), 'id, fullname');
				// Save the course info
				// (or remember 'false' that we couldn't find this course)
				$courses[$courseIDNumber] = $course;
			}

			if (empty($course)) {
				continue;
			}

			foreach ($groups as $groupName) {

				//Until new groups are added
				$groupName = rtrim($groupName, '-abc');

				// Load the group info
				$group = $DB->get_record('groups', array('name' => $groupName), 'id, name');

				if (!$group) {
					// Log an error maybe?
					continue;
				}

				if (empty($classes[$course->id])) {
					$classes[$course->id] = array();
					$classes[$course->id]['course'] = $course;
					$classes[$course->id]['groups'] = array();
				}

				// Get the teacher's name
				$classes[$course->id]['groups'][$group->id] = array(
					'id' => $group->id,
					'name' => $group->name,
				);

				if (isset($this->timetable->teacher_names) && is_array($this->timetable->teacher_names->{$groupName})) {
					$classes[$course->id]['groups'][$group->id]['teacher'] =
						$this->timetable->teacher_names->{$groupName}[0]->first
						 . ' '
						 . $this->timetable->teacher_names->{$groupName}[0]->last;
				}
			}
		}

		return $classes;
	}
}
