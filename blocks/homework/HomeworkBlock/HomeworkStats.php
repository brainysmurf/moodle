<?php

/**
 * Class for calculating stats about a set of courseids
 */

namespace SSIS\HomeworkBlock;

class HomeworkStats
{
	private $courseIDs;
	private $groupIDs;
	private $startDate;
	private $endDate;
	private $hwblock;

	function __construct($hwblock)
	{
		$this->hwblock = $hwblock;
		$this->startDate = new \DateTime('monday this week');
		$this->endDate = new \DateTime('sunday this week');
	}

	function setCourseIDs($courseIDs)
	{
		$this->courseIDs = $courseIDs;
	}

	function getCourseIDs()
	{
		return $this->courseIDs;
	}

	function setGroupIDs($groupIDs)
	{
		$this->groupIDs = $groupIDs;
	}

	function getGroupIDs()
	{
		return $this->groupIDs;
	}

	/**
	 * Set the date range to calculate stats for
	 * @param string $startDate Y-m-d format
	 * @param string $endDate   Y-m-d format
	 */
	function setDates(\DateTime $startDate, \DateTime $endDate)
	{
		$this->startDate = $startDate;
		$this->endDate = $endDate;
	}

	function getStartDate()
	{
		return $this->startDate;
	}

	function getEndDate()
	{
		return $this->endDate;
	}

	function getHomework()
	{
		$assignedRangeStart = $this->startDate->format('Y-m-d');
		$assignedRangeEnd = $this->endDate->format('Y-m-d');
		$distinct = false;
		$homework = $this->hwblock->getHomework(
			$this->groupIDs, //groupIDs
			$this->courseIDs, //courseIDs
			null, //assignedFor
			true, //approved
			false, //distinct
			null, //past
			null, //dueDate
			null, //order
			$assignedRangeStart,
			$assignedRangeEnd,
			false //exclude private
		);
		return $homework;
	}
}
