<?php

/*
	This script will update the self enrolment method for each course in the Activities category to allow parents to enrol children
*/

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/clilib.php'); 
require_once($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->libdir.'/coursecatlib.php');
require_once($CFG->libdir.'/enrollib.php');
require_once('../../enrol/locallib.php');

	
//Get all courses in Activities category

$category = coursecat::get(1);
$courses = $category->get_courses(array('recursive'=>true));

$selfenrolment = enrol_get_plugin('self');

foreach ($courses as $course) {

	echo "\n" . $course->fullname . '...';
	
	$enrolment_instances = enrol_get_instances($course->id, true);
	
	foreach ($enrolment_instances as $instance) {

		if ($instance->enrol == 'self') {
			echo "\n\tSelf enrolment instance ID: " . $instance->id;
			
			$row = new stdClass();
			$row->id = $instance->id;
			$row->customint8 = 1;
			
			$DB->update_record('enrol', $row);
		}
		
	}

}

echo "\n";

?>
