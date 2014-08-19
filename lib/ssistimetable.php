<?php

/**
* For reading data from the ssis_timetable_data table
*/

namespace SSIS;

class Timetable
{
	private static $timetableProfileFieldID = 18;

	private $userid;
	private $timetable;
	static $days = array(
		'A' => 'Mon',
		'B' => 'Tue',
		'C' => 'Wed',
		'D' => 'Thu',
		'E' => 'Fri',
	);
	static $twoWeekDays = array(
		'A' => 'Mon A',
		'B' => 'Tue A',
		'C' => 'Wed A',
		'D' => 'Thu A',
		'E' => 'Fri A',
		'F' => 'Mon B',
		'G' => 'Tue B',
		'H' => 'Wed B',
		'I' => 'Thu B',
		'J' => 'Fri B',
	);

	public function __construct($userid = false)
	{
		$this->userid = $userid;
	}

	private function query($activeOnly = false, $where = array(), $grade = null)
	{
		global $DB;
		$sql = 'SELECT
			DISTINCT(tt.name) AS name,
			grp.id AS id,
			crs.id AS courseid,
			crs.fullname AS coursename,
			CONCAT(teacher.firstname, \' \', teacher.lastname) AS teacher,
			tt.period
		FROM {ssis_timetable_info} tt
		JOIN {groups} grp ON grp.name = regexp_replace(tt.name, \'-[a-z]$\', \'\') OR grp.name = tt.name OR regexp_replace(tt.name, \'1112-[a-z]$\', \'11\') = grp.name OR regexp_replace(tt.name, \'1112-[a-z]$\', \'12\') = grp.name
		JOIN {course} crs ON crs.id = grp.courseid
		JOIN {user} teacher ON teacher.id = tt.teacheruserid';

		if ($activeOnly) {
			$where['active'] = 1;
		}

		$and = false;
		$params = array();

		if (count($where) > 0) {

			foreach ($where as $key => $value) {

				$sql .= $and ? ' AND' : ' WHERE';

				$sql .= ' ' . $key .' = ?';
				$params[] = $value;

				$and = true;
			}
		}

		if (!is_null($grade)) {
			$sql .= $and ? ' AND' : ' WHERE';
			$sql .= ' ? = ANY (string_to_array(grade, \',\'))';
			$and = true;
			$params[] = $grade;
		}

		$sql .= ' ORDER BY coursename, tt.name';

		$rows = $DB->get_records_sql($sql, $params);

		return $this->formatTimetableData($rows);
	}

	public function getTeacherClasses($activeOnly = false)
	{
		return $this->query($activeOnly, array('teacheruserid' => $this->userid));
	}

	public function getStudentClasses($activeOnly = false)
	{
		return $this->query($activeOnly, array('studentuserid' => $this->userid));
	}

	public function getAllClasses($activeOnly = false, $grade = null)
	{
		return $this->query($activeOnly, array(), $grade);
	}

	/**
	 * Takes the rows from the timetable in the database and puts it into the format used
	 * by the block
	 */
	private function formatTimetableData($rows)
	{
		$classes = array();

		foreach ($rows as $group) {

			if (!isset($classes[$group->courseid])) {
				$classes[$group->courseid] = array();

				$course = new \stdClass();
				$course->id = $group->courseid;
				$course->fullname = $group->coursename;

				$classes[$group->courseid]['course'] = $course;
				$classes[$group->courseid]['groups'] = array();
			}

			$classes[$group->courseid]['groups'][$group->id] = array(
				'id' => $group->id,
				'name' => $group->name,
				'teacher' => $group->teacher,
				'classname' => $this->getClassName($group)
			);
		}

		return $classes;
	}

	/**
	 * Returns  unique name for each class in the timetable
	 */
	private function getClassName($class)
	{
		// Teacher's name
		$name = $class->teacher;

		// and the days they meet
		$periods = $this->parsePeriodString($class->period);

		$days = array_keys($periods);
		sort($days);

		$classDays = array_map(function($day) {
			return  \SSIS\Timetable::$days[$day];
		}, $days);

		$name .= ' (' . implode(', ', $classDays) . ')';

		global $SESSION;
		if ($SESSION->userIsTeacher) {
			$name .= ' (' . $class->name . ')';
		}

		return $name;
	}

	private function parsePeriodString($periods)
	{
		$periods = str_replace('-', ',', $periods);

		$results = array();

		foreach (explode(' ', $periods) as $day) {

			$res = preg_match('/([0-9,?]+)\(([A-J,?]+)\)/', $day, $matches);

			foreach (explode(',', $matches[2]) as $dayOfWeek) {

				if (!isset($results[$dayOfWeek])) {
					$results[$dayOfWeek] = array();
				}

				foreach (explode(',', $matches[1]) as $period) {
					$results[$dayOfWeek][] = $period;
				}
			}

		}

		return $results;
	}
}
