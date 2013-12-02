<?php

/*
	Redirect to the course with the shortname OLP:CURRENTUSERID
*/
require_once(dirname(__FILE__) . '/config.php');
require_once($CFG->basedir . 'course/lib.php');

if ($olpCourseID = get_olp_courseid($USER->idnumber)) {
	redirect('/course/view.php?id=' . $olpCourseID);
}

$PAGE->set_url('/my-olp.php');
$PAGE->set_title('My Online Portfolio');
$PAGE->set_heading('My Online Portfolio');

require_login();

echo $OUTPUT->header();

	echo $OUTPUT->errorbox('Sorry, we couldn\'t find an online portfolio course for you on DragonNet');

echo $OUTPUT->footer();
