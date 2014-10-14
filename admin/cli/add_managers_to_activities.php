<?php

/**
 * This goes through every activity and adds the activiesHEAD cohort sync enrolment method
 * if it is not already present
 */

define('CLI_SCRIPT', 1);
require dirname(dirname(__DIR__)) . '/config.php';

require $CFG->dirroot . '/local/activities_hub/ActivityCenter/Data.php';
$activityData = new \SSIS\ActivityCenter\Data();

require_once($CFG->libdir.'/coursecatlib.php');

// Load all activities

// This way doesn't include hidden courses, remember
#$category = coursecat::get(1);
#$courses = $category->get_courses(array('recursive'=>true));

// This way does
// doing it this way includes hidden courses
$categoryID = 1;
$context = context_coursecat::instance($categoryID);
$contextID = $context->id;
$sql = 'select crs.id, crs.fullname from {course} crs
join {context} ctx on ctx.instanceid = crs.id and ctx.contextlevel = 50
where ctx.path like \'/1/' . $contextID .'/%\'';
$courses = $DB->get_records_sql($sql);

foreach ($courses as $course) {

	echo "\n{$course->fullname}";
	if (!$activityData->doesCourseHaveActivitesHeadSync($course)) {
		echo "\tAdding\t";
		echo $activityData->addActivitiesHeadCohortSync($course);
	} else {
		echo "\tHas cohort sync";
	}
}

