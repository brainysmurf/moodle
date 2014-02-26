<?php

/**
 * This script will enrol every student as a 'Teacher' in
 * the course that has the idnumber "OLP:[IDNUMBER]" if the
 * course exists
 */

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once('../../enrol/locallib.php');

//roleID to use
//3 = Teacher
$roleID = 3;

// Get all students
$students = $DB->get_records_sql('
SELECT * FROM {user}
WHERE email LIKE \'%@student.ssis-suzhou.net\'
AND auth != \'nologin\'
');

foreach ($students as $user) {

	$line = "\n" . $user->username;

	if (!$user->idnumber) {
		$line .= "\tUser has no idnumber";
		echo $line;
		continue;
	}

	$line .= "\tID Number:" . $user->idnumber;

	// Get the course
	$courseIDNumber = 'OLP:' . $user->idnumber;
	$course = $DB->get_record('course', array('idnumber' => $courseIDNumber));
	if (!$course) {
		$line .= "\t No course with the idnumber {$courseIDNumber} was found";
		echo $line;
		continue;
	}

	$line .= "\tCourseID:" . $course->id;

	// Check if the user is enrolled
	$courseContext = context_course::instance($course->id);

	$checkCapability = 'moodle/course:manageactivities';
	if (is_enrolled($courseContext, $user, $checkCapability, true)) {
		//User is already enrolled
		continue;
	}

	//Create an enrolment manager for the course
	$manager = new course_enrolment_manager($PAGE, $course);

	//Get the enrolment method ID for the manual enrolment method for this course
	$enrolMethod = $DB->get_record('enrol', array('enrol'=>'manual', 'courseid'=>$course->id), '*', IGNORE_MISSING);
	if (!$enrolMethod) {
		echo $line;
		echo "\tNo manual enrolment method found for course $courseid";
		continue;
	}

	echo $line;
	echo "\tUser not enroled in course";


	$enrolMethodID = $enrolMethod->id;

	//Create a manual enrolment plugin object
	$plugins = $manager->get_enrolment_plugins();
  	$plugin = $plugins['manual'];

  	$instances = $manager->get_enrolment_instances();
	$instance = $instances[$enrolMethodID];

	echo "\tEnrolling user in course $courseid";

	$today = time();
	$timestart = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);
	$timeend = 0;

	$plugin->enrol_user($instance, $user->id, $roleID, $timestart, $timeend);
}

echo "\n";
