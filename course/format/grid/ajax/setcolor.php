<?php

require_once('../../../../config.php');
require_once($CFG->dirroot.'/course/lib.php');

header('Content-type: application/json');

$courseid = required_param('courseid', PARAM_INT); //Course ID
$sectionid = required_param('sectionid', PARAM_INT); //Section ID
$color = required_param('color', PARAM_RAW);

//Get course by ID
if ( !$course = $DB->get_record("course", array("id" => $courseid)) )
{
	die(json_encode(array('error'=>'Could not find that course')));
}

//Get section
if ( !$section = $DB->get_record("course_sections", array("course" => $courseid , "id" => $sectionid)) )
{
	die(json_encode(array('error'=>'Could not find that section in that course')));
}

//Make sure user has permission to edit sections
require_login($course->id);
$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('moodle/course:update', $context);

$color = ltrim($color, '#');
$color = substr($color, 0 , 6);

//Update database
if ( $row = $DB->get_record("format_grid_icon", array("sectionid" => $sectionid)) ) {

	//Update row
	$action = 'update';
	$row->color = $color;
	$success = $DB->update_record_raw('format_grid_icon', $row);
	
} else {

	//New row
	$action = 'insert';
	$row = new stdClass();
	$row->sectionid = $sectionid;
	$row->color = $color;
	$success = $DB->insert_record('format_grid_icon', $row);
	
}

//Make the new gradient
require '../renderer.php';
$style = format_grid_renderer::make_button_gradient_style($color);

echo json_encode(array('action' => $action, 'success' => $success, 'style' => $style));

?>