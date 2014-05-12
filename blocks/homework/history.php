<?php

/**
 * List of all homework page
 */

require 'include/header.php';
echo $OUTPUT->header();
echo $hwblock->display->tabs('history');

switch ($hwblock->mode()) {

	case 'student':
	case 'parent':
	case 'teacher':

		echo $hwblock->display->sign('th-list', 'Homework History', 'All Homework, Sorted By Due Date (Latest At The Top)');

		// Get the user's group (class) IDs
		$groupIDs = $hwblock->getUsersGroupIDs($USER->id);

		$approvedStatus = true; // Only show approved homework
		$past = null; // Include future and past
		$order = 'hw.duedate DESC'; // Latest due date at the top

		$homework = $hwblock->getHomework($groupIDs, false, false, $approvedStatus, true,  $past, false, $order);

		echo $hwblock->display->homeworkList($homework, 'duedate', 'Due on ', 'l M jS Y', false, true);

		break;

	case 'pastoral':

		// What to show here?

		break;

}

echo $OUTPUT->footer();

