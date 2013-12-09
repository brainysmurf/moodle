<?php

/*

	This script will enrol the parents of students into the activities the student is enrolled in
	
	Students are found by getting all members of the studentsALL cohort
	
	Usage:
	cd /admin/cli
	php enrol_students_parents_into_activities.php
		
*/

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/clilib.php'); 
require_once($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->libdir.'/coursecatlib.php');
require_once('../../enrol/locallib.php');

	//Get all students
	$student_cohort = $DB->get_record('cohort', array('idnumber'=>'studentsALL'), 'id', MUST_EXIST);
	$students = $DB->get_records('cohort_members', array('cohortid'=>$student_cohort->id), 'userID ASC', 'userid');
		
	//Get parent role
	$parent_role = $DB->get_record('role', array('shortname'=>'parent'), 'id', MUST_EXIST);
	define('PARENT_ROLE_ID', $parent_role->id);
	
	$self_enrol_plugin = enrol_get_plugin('self');
	
	$errors = array();
	
	foreach ($students as $student)
	{
		//Get user
		$user = $DB->get_record('user', array('id' => $student->userid));
		echo "\nStudent ".$student->userid ."\t" . $user->username;

		//Get student's parents
		$parents = get_users_parents($student->userid);
		
		if (count($parents) < 1) {
			$errors[$user->username] = 'No parents';
			echo "\n\tStudent has no parents";
			continue;
		}
		
		//Get student's activities
		$activities = $DB->get_records_sql("
select 
	crs.id,
	crs.fullname
from {enrol} enrl
join {user_enrolments} usrenrl
    on usrenrl.enrolid = enrl.id
join {course} crs
    on enrl.courseid = crs.id
join {user} usr
    on usrenrl.userid = usr.id
join {course_categories} ccat
    on crs.category = ccat.id
where enrl.enrol = 'self' and
    ccat.path like '/1%' and
    usr.id = ?
    ", array($student->userid));
    
    	if (count($activities) < 1) {
			$errors[$user->username] = 'No activities';
    		echo "\n\tStudent has no activities";
    		continue;
    	}
    	
    	foreach ($activities as $activity) {
    	
    		echo "\n\t" . $activity->fullname;
    	
    		//Get enrolment instance for course
    		$instance = $DB->get_record('enrol', array('courseid' => $activity->id, 'enrol' => 'self'));
    		if (!$instance) {
    			echo "\n\tSelf enrolment instance not found for course";
    			continue;
    		}
    	
			foreach ($parents as $parent) {
			
				echo "\n\t\t" . $parent->firstname . ' ' .$parent->lastname;
			
				//Check if parent is enrolled
				if (enrol_user_is_enrolled($parent->userid, $instance->id)) {
				
					echo " already enrolled.";
				
				} else {
				
					echo " enrolling...";
			
					$self_enrol_plugin->enrol_user($instance, $parent->userid, PARENT_ROLE_ID);
					
				}
				
			} //end foreach parent
    	
    	} // end foreach activity
    
	}
	
	echo "\n";
	
	/*
	This was for checking that errors for students of class 20 and less were about no activities and not no parents
	echo "\n==========\n";
	echo "Error summary\n";
	$errors_by_year = array();
	foreach ($errors as $username => $error)
	{
		$year = substr($username,-2);
		$errors_by_year[$year][$username] = $error;
	}
	
	print_r($errors_by_year);*/

?>
