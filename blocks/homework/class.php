<?php

/**
 * Display all homework due in a class (group)
 */

require 'include/header.php';

$groupid = required_param('groupid', PARAM_INT);

$group = $DB->get_record('groups', array('id' => $groupid), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $group->courseid), '*', MUST_EXIST);

require_once $CFG->dirroot . '/course/lib.php';
$courseIcon = course_get_icon($course->id);

echo $OUTPUT->header();
echo $hwblock->display->tabs();

$mode = $hwblock->mode();

echo '<h2 style="float:right; margin:17px 10px 0;"><i class="icon-group"></i> ' . $group->name . '</h2>';
echo '<h2>' . ($courseIcon ? '<i class="icon-' . $courseIcon . '"></i> ' : '') . $course->fullname . '</h2>';

if ($mode == 'teacher' || $mode == 'pastoral') {
	$pendingHomework = $hwblock->getHomework(array($group->id), false, false, false);
	echo '<h3><i class="icon-pause"></i> Pending Homework For This Class</h3>';
	echo $hwblock->display->homeworkList($pendingHomework);
}

echo '<h3><i class="icon-bell"></i> Homework Due For This Class</h3>';
$homework = $hwblock->getHomework(array($group->id), false, false, true);
echo $hwblock->display->homeworkList($homework);

echo '<h3><i class="icon-calendar"></i> Previous Homework For This Class</h3>';
$homework = $hwblock->getHomework(array($group->id), false, false, true, true, true);
echo $hwblock->display->homeworkList($homework);

echo $OUTPUT->footer();
