<?php

require '../../config.php';
require './ActivityCenter/ActivityCenter.php';

use \SSIS\ActivityCenter\ActivityCenter;

$activityCenter = new ActivityCenter();

$mode = required_param('mode', PARAM_RAW);

if ($activityCenter->setCurrentMode($mode)) {
	redirect(ActivityCenter::PATH);

} else {

	die('Invalid mode.');

}
