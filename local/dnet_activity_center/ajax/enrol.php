<?php

require '../../../config.php';

require_login();

$courseID = required_param('courseid', PARAM_RAW);
$action = required_param('action', PARAM_RAW);

require '../ActivityCenter/ActivityCenter.php';
$activityCenter = new \SSIS\ActivityCenter\ActivityCenter();

switch ($action) {
    case 'enrol':

        $success = $activityCenter->data->addManager($courseID, $activityCenter->getUserID());

        break;
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode(array('success' => $success));
