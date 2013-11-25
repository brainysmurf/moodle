<?php

/*
	This script will unenrol all students and parents that are self-enroled in activities
*/

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/clilib.php'); 
require_once($CFG->libdir.'/coursecatlib.php');
require_once($CFG->libdir.'/enrollib.php');
	
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
			
			//Now get all the users
			
			$users = $DB->get_records('user_enrolments', array('enrolid'=>$instance->id), 'userid');
			echo "\n\t" . count($users) . " users enroled by this method.";
			
			foreach ($users as $user) {
				echo "\n\t\tUnenroling user {$user->userid}";
				$selfenrolment->unenrol_user($instance, $user->userid);
			}
		}
		
	}

}

echo "\n";

?>
