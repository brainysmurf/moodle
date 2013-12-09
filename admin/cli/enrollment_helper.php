<?php
define('CLI_SCRIPT',1);

require_once('phpclimoodle.php');
require_once( '../../config.php');
require_once($CFG->libdir.'/clilib.php');

$moodle = new moodlephp('');

$homeroom = cli_input('Enter homeroom: ');
$course_idnumber = cli_input('Enter course idnumber: ');
$group_name = cli_input('Enter group name: ');
$clear_yes_no = cli_input('Clear this group before adding?: ');

if ($clear_yes_no === 'Y') {
	$moodle->remove_all_users_from_group(array(0=>$group_name));
}

global $DB;

$students = $DB->get_records('user', array('department'=>$homeroom));

foreach ($students as $student) {
	$args = array(0=>$student->idnumber, 
				  1=>$course_idnumber,
				  2=>$group_name,
				  3=>'Student');
	echo $moodle->enrol_user_in_course($args);		
}