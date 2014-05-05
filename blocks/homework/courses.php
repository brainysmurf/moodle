<?php

/**
 * Display a list of the courses the user is enrolled in
 * It is preferable to use the classes.php page which shows groups instead.
 * This is just here in case it's needed someday
 */

require 'include/header.php';
echo $OUTPUT->header();

echo $hwblock->display->tabs('courses');

switch ($hwblock->mode()) {

	case 'student':
	case 'parent':

		echo '<h2><i class="icon-magic"></i> My Courses</h2>';
		$courses = $hwblock->getUsersCourses($hwblock->userID());
		echo $hwblock->display->courseList($courses);

		break;

	case 'teacher':

		echo '<h2><i class="icon-magic"></i> Courses I Teach</h2>';
		$teacherRoleID = 3;
		$courses = $hwblock->getUsersCourses($hwblock->userID(), $teacherRoleID);
		echo $hwblock->display->courseList($courses);

		break;

	case 'pastoral':

		 // Show all courses in the school
		echo '<h2><i class="icon-magic"></i> All Courses</h2>';

		$courses = $hwblock->getAllCourses();

		echo $hwblock->display->courseList($courses);

		break;
}

echo $OUTPUT->footer();
