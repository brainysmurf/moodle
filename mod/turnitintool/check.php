<?php

/**
 * Check that the connection to the Turnitin API is working
 * Outputs a JSON object with the result
 */

if (php_sapi_name() == 'cli') {
define('CLI_SCRIPT', true);
}

//Moodle config
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

error_reporting(0);
ini_set('display_errors', 0);

function getDebugTime()
{
	$timer = explode(' ', microtime());
	$timer = $timer[1] + $timer[0];
	return $timer;
}

function debugTimeTaken($startTime)
{
	return round((getDebugTime() - $startTime), 4);
}

//Turnitin lib
require_once("lib.php");

//Get admin user
$user = $DB->get_record('user', array('id' => 2));

$post = new stdClass();
$post->utp='2';

$startTime = getDebugTime();

$results = array(
	'startTime' => date('r'),
);

$loaderbar = null;
$tii = new turnitintool_commclass(turnitintool_getUID($user),$user->firstname,$user->lastname,$user->email,2, $loaderbar);
try {
	$tii->startSession();
	$result=$tii->createUser($post,get_string('connecttesting','turnitintool'));
	$rcode=$tii->getRcode();
	$rmessage=$tii->getRmessage();
	$tiiuid=$tii->getUserID();
	$tii->endSession();
} catch(moodle_exception $e) {
	$rcode = '';
}

$results['code'] = $rcode;
$results['speed'] = debugTimeTaken($startTime);

if ($rcode >= TURNITINTOOL_API_ERROR_START OR empty($rcode)) {
	$results['status'] = 'fail';
	mail('anthonykuske@gmail.com', 'Turnitin Broke', print_r($results, true));
} else {
	$results['status'] = 'ok';
}

echo json_encode($results) . "\n";
