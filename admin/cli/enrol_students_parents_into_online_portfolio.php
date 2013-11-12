<?php

/*

	This script will enrol all the users that are in a cohort into all the courses that are in a category

	Usage:
	cd /admin/cli
	php enrol_cohort_in_category.php cohortIDNumber categoryID roleName
	
	e.g.
	php enrol_cohort_in_category.php studentsSEC 87 Teacher
	
*/

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/clilib.php'); 
require_once($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->libdir.'/coursecatlib.php');
require_once('../../enrol/locallib.php');

        $prefix = 'OLP:';

	//Look through everyone who is in studentsALL
	$cohortID = $argv[1];
	$cohort = $DB->get_record('cohort',array('idnumber'=>'studentsALL'));
	
	if ( !$cohort )
	{
		die("\nCohort not found.\n");
	}
	
	echo "\nCohort: ".$cohort->name." (".$cohort->idnumber.")";
	
	//Get members of cohort
	$user_ids = array();
	$cohort_members = $DB->get_records('cohort_members',array('cohortid'=>$cohort->id));
	foreach ( $cohort_members as $member )
	{
		$user_ids[] = $member->userid;
	}
	
	if ( count($user_ids) < 1 )
	{
		die("\nThere are no members of this cohort.\n");
	}
	else
	{
		echo "\n".number_format(count($user_ids))." users in this cohort.";
	}
		
	// Set up roles
	$teacher_role = $DB->get_record( 'role', array('shortname'=>'editingteacher'));
	$parent_role = $DB->get_record( 'role', array('shortname'=>'student'));	

	if ( !$teacher_role )
	{
		die("\nRole not found\n");
	}
	else
	{
		echo "\nRole: ".$teacher_role->name." (".$teacher_role->id.")";
	}
	if ( !$parent_role )
	{
		die("\nRole not found\n");
	}
	else
	{
		echo "\nRole: ".$parent_role->name." (".$parent_role->id.")";
	}
	
	echo "\n\nAll users in to cohort ".$cohort->name." (".$cohort->idnumber.") \n and their parents will be enrolled into their Online Portfolio (if it exists) as a ".$teacher_role->name."\n";
	
	$response = cli_input("Enter Y to continue.");
	
	if ( trim(strtoupper($response)) !== 'Y' ) { die(); }
	
	
	//Go time
	
	foreach( $user_ids as $userid )
	{
		echo "\nUser $userid...";

		$parentid = substr($userid, 0, -1).'P';
		
		//Get full course data from DB
		$course = $DB->get_record('course',array('idnumber'=>$prefix.$userid));
		
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
			
		echo "\nEnrolling user $userid in course $courseid";

		cli_input("GO!.");
			
		$today = time();
		$timestart = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);
		$timeend = 0;

		$plugin->enrol_user($instance, $userid, $teacher_role->id, $timestart, $timeend);
		$plugin->enrol_user($instance, $parentid, $student_role->id, $timestart, $timeend);
		}
	
	echo "\n";

?>
