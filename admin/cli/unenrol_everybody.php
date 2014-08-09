<?php

/**
 * With everybody form the studentsALL cohort:
 * Unenrols them from all the Teaching and Learning courses they are enrolled in.
 * EXCEPT if they are in grade 11.
 *
 * With everybody from the teachersALL cohort:
 * Unenrols them from all the Teaching and Learning courses they are enrolled in,
 * EXCEPT if they are enrolled as a Non-Editing Teacher in that course.
 */


define('CLI_SCRIPT', true);

require dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once $CFG->libdir . '/clilib.php'; // cli only functions
require_once $CFG->dirroot . '/cohort/lib.php';
require_once $CFG->dirroot . '/course/lib.php';

echo "\nWarning! This will unenrol (almost) EVERYBODY! You probably only mean to run this once at the start of each year.\n";

$response = cli_input("Enter Y to continue or any other key to cancel.");
if (trim(strtoupper($response)) !== 'Y') {
	die();
}

// Returns the IDs of all courses in the Teaching & Learning category
$courseIDs = get_teaching_and_learning_ids();

// Preload the manual enrolment instances and contexts for each course
$manualEnrolmentInstances = array();
$courseContexts = array();

foreach ($courseIDs as $courseID) {

	// Find the manual enrolment instance for each course
	$enrolmentInstances = enrol_get_instances($courseID, true);
	foreach ($enrolmentInstances as $instance) {
		if ($instance->enrol == 'manual') {
			$manualEnrolmentInstances[$courseID] = $instance;
			break;
		}
	}

	// Get the context
	$courseContexts[$courseID] = context_course::instance($courseID);
}

// Manual enrolment plugin
$manualEnrolment = enrol_get_plugin('manual');

// Get all studentsALL students
$cohortID = cohort_get_id('studentsALL');
$cohortMembers = $DB->get_records('cohort_members', array('cohortid' => $cohortID));
foreach ($cohortMembers as $student) {

	// Is this student a grade 11?
	// Look this up from the homeroom (department) user profile field
	$homeroom = $DB->get_field('user', 'department', array('id' => $student->userid));
	$grade = intval($homeroom);
	// Skip grade 11s
	if ($grade == 11) {
		continue;
	}

	// What courses is this student enrolled in?
	$enrolledCourses = enrol_get_all_users_courses($student->userid);
	
	foreach ($enrolledCourses as $enrolledCourse) {
		// Is it a T&L course?
		if (in_array($enrolledCourse->id, $courseIDs)) {
			// Unenrol user
			echo "\nRemoving student {$student->userid} from course {$enrolledCourse->id}  {$enrolledCourse->fullname}";
			try {
				$manualEnrolment->unenrol_user($manualEnrolmentInstances[$enrolledCourse->id], $student->userid);
			} catch (Exception $e) {
				echo "\n\tUnable to do that";
			}
		}
	}

}

echo "\n\n";

// 3 = teacher 
// 4 = non-editing teacher 
// 
// context level 50 = a course

// Get all teachersALL teachers
$cohortID = cohort_get_id('teachersALL');
$cohortMembers = $DB->get_records('cohort_members', array('cohortid' => $cohortID));
foreach ($cohortMembers as $teacher) {

	// What courses is this teacher enrolled in?
	$enrolledCourses = enrol_get_all_users_courses($teacher->userid);
	
	foreach ($enrolledCourses as $enrolledCourse) {
		// Is it a T&L course?
		if (in_array($enrolledCourse->id, $courseIDs)) {

			$contextID = $courseContexts[$courseID]->id;

			// Check if the user is a non-editing teacher
			if ($DB->get_records('role_assignments', array('userid' => $teacher->userid, 'contextid' => $contextID, 'roleid' => 4))) {
				echo "\nTeacher {$teacher->userid} is a non-editing teacher in {$enrolledCourse->id}  {$enrolledCourse->fullname}, skipping course";
			} else {
				// Unenrol user
				echo "\nRemoving teacher {$teacher->userid} from course {$enrolledCourse->id} {$enrolledCourse->fullname}";
				try {
					$manualEnrolment->unenrol_user($manualEnrolmentInstances[$enrolledCourse->id], $teacher->userid);
				} catch (Exception $e) {
					echo "\n\tUnable to do that";
				}
			}
		}
	}
}

echo "\n";
