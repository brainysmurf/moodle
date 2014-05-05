<?php

/**
 * Display a list of the classes (groups) the user is enrolled in
 */

require 'include/header.php';
echo $OUTPUT->header();

echo $hwblock->display->tabs('classes');

switch ($hwblock->mode()) {

	case 'student':
	case 'teacher':
		/**
		 * Show the timetable view of the student's homework due in the next 2 weeks
		 */

		echo '<h2><i class="icon-magic"></i> My Classes</h2>';
		$classes = $hwblock->getUsersGroups($USER->id);
		echo $hwblock->display->classList($classes, $USER->id);

		break;

	case 'parent':

		break;

	case 'pastoral':

		break;
}

echo $OUTPUT->footer();
