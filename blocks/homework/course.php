<?php

// This isn't used

/**
 * Display all homework due in a course
 */

require 'include/header.php';

$courseid = required_param('courseid', PARAM_INT);
$userid = optional_param('userid', '', PARAM_INT); //or groupid instead?
$action = optional_param('action', 'view', PARAM_RAW); //or groupid instead?

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_once $CFG->dirroot . '/course/lib.php';
$courseIcon = course_get_icon($course->id);

echo $OUTPUT->header();
echo $hwblock->display->tabs();

$mode = $hwblock->mode();

echo '<h2 style="float:right; margin:17px 10px 0;"><i class="icon-group"></i> Showing All Classes In Course</h2>';
echo '<h2>' . $course->fullname . '</h2>';

if ($mode == 'teacher' || $mode == 'pastoral') {
	$pendingHomework = $hwblock->getHomework(false, array($course->id), false, false);
	echo '<h3><i class="icon-list"></i> Pending Homework For This Course</h3>';
	echo $hwblock->display->homeworkList($pendingHomework, false, false, false, true);
}

echo '<h3><i class="icon-bell"></i> Homework Due For This Course</h3>';
$homework = $hwblock->getHomework(false, array($course->id), false, true);
echo $hwblock->display->homeworkList($homework, false, false, false, true);

echo '<h3><i class="icon-calendar"></i> Previous Homework For This Course</h3>';
$homework = $hwblock->getHomework(false, array($course->id), false, true, true, true);
echo $hwblock->display->homeworkList($homework, false, false, false, true);

echo $OUTPUT->footer();
