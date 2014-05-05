<?php

/**
 * List of all homework page
 */

require 'include/header.php';
echo $OUTPUT->header();
echo $hwblock->display->tabs('history');

switch ($hwblock->mode()) {

	case 'student':
	case 'teacher':

		echo '<h2><i class="icon-time"></i>  All Homework, Sorted By Due Date (Latest At The Top)</h2>';


		// Get the user's group (class) IDs
		$groupIDs = $hwblock->getUsersGroupIDs($USER->id);

		#$approvedStatus = $hwblock->mode() == 'student' ? true : null;
		$approvedStatus = true;
		$past = null; // Include future and past
		$order = 'hw.duedate DESC'; // Latest due date at the top
		$homework = $hwblock->getHomework($groupIDs, false, false, $approvedStatus, true,  $past, false, $order);

		echo $hwblock->display->homeworkList($homework, 'duedate', 'Due on ');

		break;

	case 'parent':

		break;

	case 'pastoral':

		break;

}

echo $OUTPUT->footer();
