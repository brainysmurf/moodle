<?php

namespace SSIS\HomeworkBlock;

class HomeworkItem
{
	private $row;
	private $assignedDates = null;

	private static $table = 'block_homework';
	private static $assignedDaysTable = 'block_homework_assign_dates';

	public function __construct($row = null)
	{
		$this->row = $row ? $row : new stdClass();
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
	public static function load($homeworkid)
	{
		global $DB;
		$row = $DB->get_record(self::$table, array(
			'id' => $homeworkid
		), '*', MUST_EXIST);
		return new HomeworkItem($row);
	}
}
