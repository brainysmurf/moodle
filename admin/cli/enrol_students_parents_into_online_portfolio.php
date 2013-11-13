<?php

/*

	This script will enrol all students into their own portfolio as an editor
	and will enrol parents into their children's portfolio as a view
	
	Assumes that the courses have already been created, and the course idnumbers are
	$prefix.$idnumber
	e.g.
	OLP:55555

	Usage:
	cd /admin/cli
	php enrol_student_into_online_portfolio.php
		
*/

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/clilib.php'); 
require_once($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->libdir.'/coursecatlib.php');
require_once('../../enrol/locallib.php');

        $prefix = 'OLP:';

	//Look through everyone who is in studentsALL
	$cohort = $DB->get_record('cohort',array('idnumber'=>'studentsALL'));
	
	if ( !$cohort )
	{
		die("\nCohort not found.\n");
	}
	
	echo "\nCohort: ".$cohort->name." (".$cohort->idnumber.")";
	
	//Get members of cohort
	$user_ids = array();
	$cohort_members = $DB->get_records('cohort_members',array('cohortid'=>$cohort->id));
			
	// Set up roles
	$teacher_role = $DB->get_record( 'role', array('shortname'=>'editingteacher'));
	$student_role = $DB->get_record( 'role', array('shortname'=>'student'));	

	if ( !$teacher_role )
	{
		die("\nRole not found\n");
	}
	else
	{
		echo "\nRole: ".$teacher_role->name." (".$teacher_role->id.")";
	}
	if ( !$student_role )
	{
		die("\nRole not found\n");
	}
	else
	{
		echo "\nRole: ".$student_role->name." (".$student_role->id.")";
	}
	
	echo "\n\nAll users in to cohort ".$cohort->name." (".$cohort->idnumber.") \n and their parents will be enrolled into their Online Portfolio (if it exists) as a ".$teacher_role->name."\n";
	
	$response = cli_input("Enter Y to continue.");
	
	if ( trim(strtoupper($response)) !== 'Y' ) { die(); }
	
	
	//Go time
	
	foreach( $cohort_members as $user )
	{
	        $student = $DB->get_record('user', array('id'=>$user->userid));
	        $userid = $student->id;
		$useridnumber = $student->idnumber;
		$parentidnumber = substr($student->idnumber, 0, -1).'P';
		$parent = $DB->get_record('user', array('idnumber'=>$parentidnumber));
		$parentid = $parent->id;

		echo "\nUser $userid, parent $parentid...";

		//Get full course data from DB
		$course = $DB->get_record('course',array('idnumber'=>$prefix.$useridnumber));
		if ($course === false) {
	            echo 'cannot';
		    continue;
		}

		$courseid = $course->id;
		//Get context
		if ( !$context = get_context_instance(CONTEXT_COURSE, $courseid, MUST_EXIST) )
		{
			die("\nCould not get context for course ".$courseid."\n");
		}
		
		//Create an enrolment manager for the course
		$manager = new course_enrolment_manager($PAGE, $course);
		
		//Get the enrolment method ID for the manual enrolment method for this course
		$enrolMethod = $DB->get_record('enrol', array('enrol'=>'manual', 'courseid'=>$courseid), '*', MUST_EXIST);
		if ( !$enrolMethod )
		{
			die("\nNo manual enrolment method found for course $courseid\n");
		}
		
		$enrolMethodID = $enrolMethod->id;
		
		//Create a manual enrolment plugin object
		$plugins = $manager->get_enrolment_plugins();
	  	$plugin = $plugins['manual'];

	  	$instances = $manager->get_enrolment_instances();
		$instance = $instances[$enrolMethodID];

		if ( is_enrolled($context, $userid) )
			{
				//This would break if the user is already enrolled but with the wrong role
				echo "\nUser $userid is already enrolled in course $courseid";			
				continue;
			}

		echo "\nEnrolling user $student->firstname $student->lastname in course $course->fullname";

		$today = time();
		$timestart = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);
		$timeend = 0;

		$plugin->enrol_user($instance, $userid, $teacher_role->id, $timestart, $timeend);
		$plugin->enrol_user($instance, $parentid, $student_role->id, $timestart, $timeend);
		}
	
	echo "\n";

?>
