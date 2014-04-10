<?php
require_once('../../config.php');

require_login();

$userid = required_param('userid', PARAM_RAW);
$text = required_param('text', PARAM_RAW);
$subject = required_param('subject', PARAM_RAW);

$from = $DB->get_record('user', array('username'=>'lcssisadmin'));
$to = $DB->get_record('user', array('id'=>$userid));
email_to_user($to, $from, $subject, $text);
