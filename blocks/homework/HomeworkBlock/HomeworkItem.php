<?php

namespace SSIS\HomeworkBlock;

class HomeworkItem
{
	private $row;
	private $assignedDates = null;

	private static $table = 'block_homework';
	private static $assignedDaysTable = 'block_homework_assign_dates';
	private static $notesTable = 'block_homework_notes';

	public function __construct($row = null)
	{
		$this->row = $row ? $row : new \stdClass();
	}

	public function __get($key)
	{
		if (isset($this->row->{$key})) {
			return $this->row->{$key};
		}
	}

	public function __set($key, $value)
	{
		if (isset($this->row->{$key})) {
			$this->row->{$key} = $value;
		}
	}

	/**
	 * Returns the underlying database row object
	 */
	public function getRow()
	{
		return $this-row;
	}

	public function getTitle()
	{
		return !empty($this->row->title)  ? $this->row->title : $this->row->coursename;
	}

	public function getGroupName()
	{
		global $DB;
		$group = $DB->get_record('groups', array('id' => $this->row->groupid));
		return $group->name;
	}

	/**
	 * Get notes made about this item by the given user ID
	 */
	public function getNotes($userID)
	{
		global $DB;
		$notes = $DB->get_field(self::$notesTable, 'notes', array('userid' => $userID, 'homeworkid' => $this->row->id));
		return $notes;
	}

	/**
	 * Save a user's notes about this item
	 */
	public function setNotes($userID, $notes)
	{
		global $DB;

		if ($record = $DB->get_record(self::$notesTable, array('userid' => $userID, 'homeworkid' => $this->row->id))) {

			$record->notes = $notes;
			return $DB->update_record(self::$notesTable, $record);

		} else {

			$record = new \stdClass();
			$record->homeworkid = $this->row->id;
			$record->userid = $userID;
			$record->notes = $notes;
			return $DB->insert_record(self::$notesTable, $record);

		}

	}


	/**
	 * Save any modifications to this object back to the database
	 */
	public function save()
	{
		global $DB;
		return $DB->update_record('block_homework', $this->row);
	}


	public function addAssignedDate($date)
	{
		global $DB;

		$dateRow = new \stdClass();
		$dateRow->homeworkid = $this->row->id;
		$dateRow->date = $date;

		if ($DB->insert_record(self::$assignedDaysTable, $dateRow)) {
			$this->assignedDates[$date] = true;
			return true;
		}
		return false;
	}

	public function removeAssignedDate($date)
	{
		global $DB;

		if ($DB->delete_records(self::$assignedDaysTable, array(
			'homeworkid' => $this->row->id,
			'date' => $date
		))) {
			unset($this->assignedDates[$date]);
			return true;
		}
		return false;
	}

	public function clearAssignedDates()
	{
		global $DB;

		if ($DB->delete_records(self::$assignedDaysTable, array(
			'homeworkid' => $this->row->id
		))) {
			$this->assignedDates = array();
			return true;
		}
		return false;
	}

	public function getAssignedDates($reload = false)
	{
		if (!$reload && !is_null($this->assignedDates)) {
			return array_keys($this->assignedDates);
		}

		global $DB;
		$assignedDates = array();
		$dateRows = $DB->get_records(self::$assignedDaysTable, array(
			'homeworkid' => $this->row->id
		));
		foreach ($dateRows as $row) {
			$assignedDates[$row->date] = true;
		}

		$this->assignedDates = $assignedDates;
		return array_keys($assignedDates);
	}

	/**
	 * Loads a HomeworkItem instance with info from the database for the given ID
	 */
	public static function load($homeworkid, $simple = false)
	{
		global $DB;
		if ($simple) {
			$row = $DB->get_record(self::$table, array(
				'id' => $homeworkid
			), '*', MUST_EXIST);
		} else {

			$sql = 'SELECT hw.*,
				crs.id AS courseid,
				crs.fullname AS coursename,
				usr.username AS username,
				usr.firstname AS userfirstname,
				usr.lastname AS userlastname
			FROM {block_homework} hw
			LEFT JOIN {course} crs ON crs.id = hw.courseid
			LEFT JOIN {user} usr ON usr.id = hw.userid
			WHERE hw.id = ?';
			$params = array(
				'id' => $homeworkid
			);

			$rows = $DB->get_records_sql($sql, $params);
			if (count($rows) < 1) {
				throw new \Exception("Couldn't find that piece of homework");
			}
			$row = reset($rows);

		}
		return new HomeworkItem($row);
	}
}
