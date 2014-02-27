<?php

/**
 * Find all courses that have turnitin assignments that are still open.
 * For each course, run the turnitintool_add_instance
 */

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(__FILE__))).'/config.php');

ini_set('display_errors', 1);

$sql = 'select course.*, count(tii.id) as turnitincount
from {course} course
left join {turnitintool} tii on tii.course = course.id
where tii.defaultdtdue > extract(epoch from now())
	--where the due date timestamp is afte the current time
group by course.id
having count(tii.id) > 0';

$courses = $DB->get_records_sql($sql);

if (count($courses) < 1) {
	exit(0);
}

// Turnitin lib
require_once($CFG->dirroot . '/mod/turnitintool/lib.php');

//Get admin user
$admin = $DB->get_record('user', array('id' => 2));

foreach ($courses as $course) {

	echo "\nCourse {$course->id} has {$course->turnitincount} open Turnitin assignemnts";

	//Get the user who is the owner of the course
	$owner = turnitintool_get_owner($course->id);
	if (is_null($owner)) {
		$owner = $admin;
	}

	$loaderbar = null;
	$tii = new turnitintool_commclass(turnitintool_getUID($owner),$owner->firstname,$owner->lastname,$owner->email,2,$loaderbar);

	// Set this course up in Turnitin or check to see if it has been already
	// Either return the stored ID OR store the New Turnitin Course ID then return it
	$turnitincourse = turnitintool_classsetup($course, $owner, get_string('classprocess','turnitintool'), $tii, $loaderbar); // PROC 2
	print_r($turnitincourse);
}

