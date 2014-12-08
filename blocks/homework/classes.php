<?php

/**
 * Display a list of the classes (groups) the user is enrolled in
 */

require 'include/header.php';
echo $OUTPUT->header();

echo $hwblock->display->tabs('classes');

switch ($hwblock->mode()) {

	case 'pastoral-student':
	case 'student':
	case 'parent':
	case 'teacher':
		/**
		 * Show the timetable view of the student's homework due in the next 2 weeks
		 */

		echo $hwblock->display->sign('group', 'View by Class', 'Find a summary of homework information (including upcoming and due) by class only.');
		$classes = $hwblock->getUsersGroups($hwblock->userID());
		echo $hwblock->display->classList($classes);

		break;

	case 'pastoral':

		/**
		 * Show all classes in the school
		 */
		echo '<h2><i class="icon-group"></i> All Classes</h2>';
		$classes = $hwblock->getAllGroups();
		echo $hwblock->display->classList($classes);

		break;
}

echo $OUTPUT->footer();
