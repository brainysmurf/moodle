<?php

/**
 * Display a list of the courses the user is enrolled in
 */

require 'include/header.php';
echo $OUTPUT->header();

echo $hwblock->display->tabs('courses');

switch ($hwblock->mode()) {
	case 'student':

		/**
		 * Show the timetable view of the student's homework due in the next 2 weeks
		 */

		//TODO: Change to new timetable courses instead of everything?

		echo '<h2><i class="icon-magic"></i> My Courses</h2>';
		$courses = $hwblock->getUsersCourses($USER->id);
		echo $hwblock->display->courseList($courses, $USER->id);

		break;

	case 'teacher':

		//TODO: Change to new timetable courses instead of everything?

		echo '<h2><i class="icon-magic"></i> Classes I Teach</h2>';
		$teacherRoleID = 3;
		$courses = $hwblock->getUsersCourses($USER->id, $teacherRoleID);
		echo $hwblock->display->courseList($courses);

		break;

	case 'parent':

		break;

	case 'pastoral':

		break;
}

echo $OUTPUT->footer();
