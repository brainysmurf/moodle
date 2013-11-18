<?php

/*

	This script will enrol all the users that are in a cohort into all the courses that are in a category
	and give them the role you specify

	Run via the command line like this:
	cd /admin/cli
	php enrol_cohort_in_category.php cohortIDNumber categoryID roleShortname
	
	e.g.
	php enrol_cohort_in_category.php studentsSEC 87 teacher
	
		Will enrol all users in the studentsSEC into all courses in category 87 (and it's subcategories) and give them the 'teacher' role
	
*/

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/clilib.php'); 
require_once($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->libdir.'/coursecatlib.php');
require_once('../../enrol/locallib.php');

	
	//Chort IDnumber
	$cohortID = $argv[1];
	$cohort = $DB->get_record('cohort',array('idnumber'=>$cohortID));
	
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
	
	//Category ID
	$categoryID = $argv[2];
	$category = coursecat::get($categoryID);
	
	if ( !$category )
	{
		die("\nCategory not found.\n");
	}
	
	echo "\nCategory: ".$category->name;
	
	//Get all courses in category and subcategory
	$courses = $category->get_courses(array('recursive'=>true));
	
	echo "\nCourses...";
	
	$course_ids = array();
	foreach ( $courses as $course )
	{
		echo "\n\t".$course->fullname;
		$course_ids[] = $course->id;
	}
	
	if ( count($course_ids) < 1 )
	{
		die("\nThere are no courses in this category or its subcategories.\n");
	}
	else
	{
		echo "\n".number_format(count($course_ids))." courses in this category (including those in subcategories).";
	}
	
	// Role to give the users
	$roleName = $argv[3];
	
	$role = $DB->get_record( 'role', array('shortname'=>$roleName));
	
	if ( !$role )
	{
		die("\nRole not found\n");
	}
	else
	{
		echo "\nRole: ".$role->name." (".$role->id.")";
		$roleid = $role->id;
	}
	
	//We ask the user to confirm to make sure we're doing the right thing
	
	echo "\n\nAll users in to cohort ".$cohort->name." (".$cohort->idnumber.") \nwill be enrolled as a \"".$role->name."\" \nin the courses in category ".$category->name."\n";
	
	$response = cli_input("Enter Y to continue. (Any other key to cancel)");
	
	//If they press something other than Y we quit.
	if ( trim(strtoupper($response)) !== 'Y' ) { die(); }
	
	
	//Go time
	
	foreach( $course_ids as $courseid )
	{
		echo "\nCourse $courseid...";
		
		//Get full course data from DB
		$course = $DB->get_record('course',array('id'=>$courseid));
		
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
		
		foreach( $user_ids as $userid )
		{
			if ( is_enrolled($context, $userid) )
			{
				//This would break if the user is already enrolled but with the wrong role
				echo "\nUser $userid is already enrolled in course $courseid";			
				continue;
			}
			
			echo "\nEnrolling user $userid in course $courseid";
			
			$today = time();
			$timestart = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);
			$timeend = 0;

			$plugin->enrol_user($instance, $userid, $roleid, $timestart, $timeend);
		}
	}
	
	echo "\n";

?>
