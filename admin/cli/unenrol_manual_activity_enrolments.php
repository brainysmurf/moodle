<?php

/**
 * Because activities now how a cohort sync enrolment method, activitiy heads are enroled twice.
 * Go through all activities and remove the manual enrolment for users in the activitiesHEAD cohort
 */

define('CLI_SCRIPT', 1);
require dirname(dirname(__DIR__)) . '/config.php';

require $CFG->dirroot . '/local/activities_hub/ActivityCenter/Data.php';
$activityData = new \SSIS\ActivityCenter\Data();

require_once($CFG->libdir.'/coursecatlib.php');

require_once $CFG->dirroot . '/cohort/lib.php';

// Load all activities

// This way doesn't include hidden courses, remember
#$category = coursecat::get(1);
#$courses = $category->get_courses(array('recursive'=>true));

// doing it this way includes hidden courses
$categoryID = 1;
$context = context_coursecat::instance($categoryID);
$contextID = $context->id;
$sql = 'select crs.id, crs.fullname from {course} crs
join {context} ctx on ctx.instanceid = crs.id and ctx.contextlevel = 50
where ctx.path like \'/1/' . $contextID .'/%\'';
$courses = $DB->get_records_sql($sql);


// Load the cohort
$cohortID = cohort_get_id('activitiesHEAD');
$cohortMembers = $DB->get_records('cohort_members', array('cohortid' => $cohortID));
print_r($cohortMembers);

$plugin = enrol_get_plugin('manual');

foreach ($courses as $course) {

	echo "\n{$course->fullname}";

	// Get manual enrolment method for the course
	$instance = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $course->id), '*', IGNORE_MISSING);

	if (!$instance) {
		echo "\tNo manual instance";
		continue;
	} else {
		echo "\tManual method: {$instance->id}";
	}

	// Unenrol these users from the manual enrolment method (they'll remain enroled from the cohort sync)
	foreach ($cohortMembers as $cohortMember) {
		echo "\t" . $plugin->unenrol_user($instance, $cohortMember->userid);
	}

}
