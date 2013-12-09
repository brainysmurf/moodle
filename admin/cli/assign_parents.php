<?php

/*
	This scrips checks every user in the studentsALL cohort.
	If the user has no parents assigned, it will look for users with the right idnumber (123P) and assign them as parents	Usage:
*/

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once('../../cohort/lib.php');
require_once('../../enrol/locallib.php');
require_once('../../group/lib.php');
require_once('../../lib/grouplib.php');

function getUserByIDNumber($idnumber)
  {
    global $DB;
    $s = $DB->get_record_select( 'user' , 'idnumber = ?', array($idnumber) );
    return $s;    
  }

function associate_child_to_parent($args)
  {
    $parent_idnumber = $args[0];
    $child_idnumber = $args[1];

    try {
      if( $parent = getUserByIDNumber( $parent_idnumber ) and $child = getUserByIDNumber($child_idnumber) ) {
	      $context = get_context_instance( CONTEXT_USER , $child->id );
        role_assign( PARENT_ROLE_ID, $parent->id, $context->id );
        return true;
      } else {
      return "-1 Either could not find parent ".$parent_idnumber." or couldn't find child ".$child_idnumber;
      }
    }

    catch( Exception $e ) {
      var_dump($e);
      return "-1 Could not associate child".$child_idnumber." to parent ".$parent_idnumber;
    }

    return "0";
  }


	//Get all students
	$student_cohort = $DB->get_record('cohort', array('idnumber'=>'studentsALL'), 'id', MUST_EXIST);
	$students = $DB->get_records('cohort_members', array('cohortid'=>$student_cohort->id), 'userID ASC', 'userid');
	
	//Get parent role
	$parent_role = $DB->get_record('role', array('shortname'=>'parent'), 'id', MUST_EXIST);
	define('PARENT_ROLE_ID', $parent_role->id);
	
	foreach ($students as $student)
	{
		//Get user
		$user = $DB->get_record('user', array('id' => $student->userid));
		echo "\nStudent ".$student->userid ."\t" . $user->username;
		
		//Get student's parents
		$parents = get_users_parents($student->userid);
		
		if (count($parents) < 1) {
		
			echo "\n\tNo parents assigned";
			
			$parent_idnumber = substr_replace($user->idnumber, 'P', -1);
			$student_idnumber = $user->idnumber;
			
			$result = associate_child_to_parent(array($parent_idnumber, $student_idnumber));
			if ($result !== true) {
				echo "\n\t".$result;
			} else {
				echo "Done";
			}
		}
		
    
	}
	
	echo "\n";


?>
