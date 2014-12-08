<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('CLI_SCRIPT', true);
include('../../config.php');
global $DB;

$user = $DB->get_record('user', array('username'=>'adammorris'));
email_to_user($user, 'Happy Student <happystudent@student.ssis-suzhou.net>', 'Test email from Moodle CLI', date('r'));
$user = $DB->get_record('user', array('username'=>'happystudent'));
email_to_user($user, 'Happy Student <happystudent@student.ssis-suzhou.net>', 'Test email from Moodle CLI', date('r'));
