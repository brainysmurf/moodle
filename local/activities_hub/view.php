<?php

/**
 * Displays a view from the views directory
 */

require '../../config.php';

require './ActivityCenter/ActivityCenter.php';
$activityCenter = new \SSIS\ActivityCenter\ActivityCenter();

$view = optional_param('view', false, PARAM_RAW);

if (!$activityCenter->isValidView($view)) {
	$view = false;
}

if (!$view) {
	$view = $view ? $view : $activityCenter->defaultViewForMode($mode);
	#redirect("view.php?view={$view}");
	exit();
}

$mode = $activityCenter->getCurrentMode();

$PAGE->set_title('Activity Center');
$PAGE->set_heading('Activity Center');

$PAGE->requires->css('/blocks/homework/assets/bootstrap/css/bootstrap.css');

echo $OUTPUT->header();

// Show mode switching tabs
echo $activityCenter->display->showTabs($mode, $view);

include "./views/{$mode}/{$view}.php";

echo $OUTPUT->footer();
