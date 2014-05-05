<?php

/**
 * Display a list of the classes (groups) the user is enrolled in
 */

require 'include/header.php';
echo $OUTPUT->header();

echo $hwblock->display->tabs('classes');

switch ($hwblock->mode()) {

	case 'student':
	case 'parent':
	case 'teacher':
		/**
		 * Show the timetable view of the student's homework due in the next 2 weeks
		 */

		echo '<h2><i class="icon-magic"></i> Classes</h2>';
		$classes = $hwblock->getUsersGroups($hwblock->userID());
		echo $hwblock->display->classList($classes);

		break;

	case 'pastoral':

		/**
		 * Show all classes in the school
		 */
		echo '<h2><i class="icon-magic"></i> All Classes</h2>';

		//TODO: Where can this list come from?
		$classes = array();

		echo $hwblock->display->classList($classes);

		break;
}

echo $OUTPUT->footer();
