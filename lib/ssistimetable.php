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
			CONCAT(teacher.firstname, \' \', teacher.lastname) AS teacher
		FROM {ssis_timetable_info} tt
		JOIN {groups} grp ON grp.name = regexp_replace(tt.name, \'-[a-z]$\', \'\') OR grp.name = tt.name
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
		return $rows;
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
}
