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
		$groupIDs = $hwblock->getUsersGroupIDs($hwblock->userID());

		// Get the homework for those groups
		$approved = true;
		$distinct = false;
		$homework = $hwblock->getHomework($groupIDs, false, false, $approved, $distinct);

		echo $hwblock->display->overview($homework, true);

		echo '<br/><br/>';

		// Show the list
		echo $hwblock->display->homeworkList($homework, 'assigneddate', 'To Do On ');

		break;

	case 'teacher':

		/**
		 * Pending homework approval page
		 */

		echo $hwblock->display->sign('check', 'Pending Submissions', 'This section shows homework that a students in your classes have submitted. Other students will NOT see these until approved by you.');
		// echo '<h2><i class="icon-list"></i> Pending Homework</h2>';
		// echo '<p>This section shows homework that a students in your classes have submitted. Other students will <strong>not</strong> see these until approved by you.</p>';

		// Get the user's group (class) IDs
		$groupIDs = $hwblock->getUsersGroupIDs($hwblock->userID());

		// Get the homework for those groups
		$approved = false;
		$distinct = true;
		$homework = $hwblock->getHomework($groupIDs, false, false, $approved, $distinct);

		// Show the list
		echo $hwblock->display->homeworkList($homework);

		break;

	case 'pastoral':

		//TODO: What should pastroal mode show on the from page. Some stats maybe?
		echo 'The pastoral homework area is still under development.';

		break;
}

echo $OUTPUT->footer();
