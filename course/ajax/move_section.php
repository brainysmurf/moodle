<?php

require_once('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');

header('Content-type: application/json');

$courseid = required_param('courseid', PARAM_INT); //Course ID
$oldPos = required_param('oldPos', PARAM_INT); // Section number
$newPos = required_param('newPos', PARAM_INT); // Section number

$site = get_site();

//Get course by ID
if ( !$course = $DB->get_record("course", array("id" => $courseid)) )
{
	die(json_encode(array('error'=>'Could not find that course')));
}

//Get section
if ( !$section = $DB->get_record("course_sections", array("course" => $courseid , "section" => $oldPos)) )
{
	die(json_encode(array('error'=>'Could not find that section in that course')));
}

//Make sure user has permission to edit sections
require_login($course->id);
$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('moodle/course:update', $context);

$success = move_section_to($course, $oldPos, $newPos);

echo json_encode(array('success'=>$success));

?>