<?php

/**
 * List of all homework page
 */

require 'include/header.php';
echo $OUTPUT->header();
echo $hwblock->display->tabs('history');

switch ($hwblock->mode()) {

	case 'pastoral-student':
	case 'student':
	case 'parent':
	case 'teacher':

		echo $hwblock->display->sign('th-list', 'View History', 'All Homework, Sorted By Due Date (Latest At The Top)');

		// Get the user's group (class) IDs
		$groupIDs = $hwblock->getUsersGroupIDs($hwblock->userID());

		$approvedStatus = true; // Only show approved homework
		$past = null; // Include future and past
		$order = 'hw.duedate DESC'; // Latest due date at the top

		$homework = $hwblock->getHomework(
			$groupIDs,
			false,
			false,
			$approvedStatus,
			true,
			$past,
			false,
			$order
		);

		echo $hwblock->display->homeworkList($homework);

		break;

	case 'pastoral':

		// What to show here?

		break;

}

echo $OUTPUT->footer();

