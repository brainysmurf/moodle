<?php

/**
 * Enrol the current user as a manager into an activity
 */

require '../../../../config.php';

require_login();

// FIXME: No permission checks here!

$courseID = required_param('courseid', PARAM_RAW);
$action = required_param('action', PARAM_RAW);

require '../../ActivityCenter/ActivityCenter.php';
$activityCenter = new \SSIS\ActivityCenter\ActivityCenter();

switch ($action) {
	case 'enrol':

		$success = $activityCenter->addManager($courseID, $activityCenter->userID());

		break;
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode(array('success' => $success));
