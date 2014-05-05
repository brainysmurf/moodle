<?php

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

echo '<h1>' . ($courseIcon ? '<i class="icon-' . $courseIcon . '"></i> ' : '') . $course->fullname . '</h1>';

echo '<h2><i class="icon-bookmark"></i> Upcoming Homework For This Course</h2>';

$approved = $mode == 'teacher' ? null : true;
$homework = $hwblock->getHomework(false, array($course->id), false, $approved);
echo $hwblock->display->homeworkList($homework);

echo $OUTPUT->footer();
