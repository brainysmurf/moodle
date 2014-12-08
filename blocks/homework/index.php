<?php

/**
 * Front page for homework block
 *
 * For students: shows 'to do' view
 * For teachers: shows 'pending submissions'
 */

require 'include/header.php';
echo $OUTPUT->header();
echo $hwblock->display->tabs('index');

switch ($hwblock->mode()) {

	case 'pastoral-student':
	case 'student':
	case 'parent':

		/**
		 * Show the timetable view of the student's homework due in the next 2 weeks
		 */

		echo $hwblock->display->sign('calendar', 'To Do', 'This page presents a two-week overview of your homework.');

		// Get the user's group (class) IDs
		$groupIDs = $hwblock->getUsersGroupIDs($hwblock->userID(), true);

		// Get the homework for those groups
		$approved = true;
		$distinct = false;
		$homework = $hwblock->getHomework(
			$groupIDs, //$groupIDs = false,
			false, //$courseIDs = false,
			false, //$assignedFor = false,
			$approved, //$approved = true,
			$distinct ,//$distinct = true,
			false, //$past = false,
			false, //$dueDate = false,
			null, //$order = null,
			null, //$assignedRangeStart = null,
			null, //$assignedRangeEnd = null,
			true //$includePrivate = false
		);

		echo $hwblock->display->overview($homework, true);

		echo '<br/><br/>';

		// Show the list
		echo $hwblock->display->homeworkList($homework, 'assigneddate', 'To Do On ', 'l M jS Y', false, false);

		echo $hwblock->display->icalFeedBox();

		break;

	case 'teacher':

		/**
		 * Pending homework approval page
		 */

		echo $hwblock->display->sign('check', 'Manage Submissions', 'This section shows homework that a students in your classes have submitted. Other students will NOT see these until approved by you.');

		// Get the user's group (class) IDs
		$groupIDs = $hwblock->getUsersGroupIDs($hwblock->userID(), true);

		// Get the homework for those groups
		$approved = false;
		$distinct = true;
		$homework = $hwblock->getHomework(
			$groupIDs,
			false,
			false,
			$approved,
			$distinct);

		// Show the list
		echo $hwblock->display->homeworkList($homework);

		break;

	case 'pastoral':

		echo $hwblock->display->sign('calendar', 'Overview', 'This page shows all homework assigned this week.');

		/**
		 * Whole school week overview
		 */

		$stats = new \SSIS\HomeworkBlock\HomeworkStats($hwblock);
		echo $hwblock->display->weekStats($stats);

		break;
}

echo '<br/>';
echo '<br/>';

$timetableList = '';
for ($i = 6; $i <= 10; $i++) {
	$timetableList .= '<a target="_blank" style="display:block; padding: 4px 2px; font-size: 14px; margin:2px 0;" class="btn" href="timetables/Grade%20'. $i . '%20Homework%20Timetable%202014-15.pdf">Grade '. $i . ' Homework Timetable</a>';
}

echo $hwblock->display->sign('list-alt', 'Homework Timetables', '<div class="clear"></div>' . $timetableList);

echo $OUTPUT->footer();
