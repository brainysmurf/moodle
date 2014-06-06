<?php

require '../../config.php';

require './ActivityCenter/ActivityCenter.php';
$activityCenter = new \SSIS\ActivityCenter\ActivityCenter();

$mode = optional_param('mode', false, PARAM_RAW);
$view = optional_param('view', false, PARAM_RAW);

$allowedModes = $activityCenter->possibleModes();

if (!$mode || !$view) {
	$mode = $mode ? $mode : $allowedModes[0];
	$view = $view ? $view : $activityCenter->defaultViewForMode($mode);
	redirect("index2.php?mode={$mode}&view={$view}");
	exit();
}

if (!in_array($mode, $allowedModes)) {
	die();
}

$PAGE->set_title('Activity Center');
$PAGE->set_heading('Activity Center');

$PAGE->requires->css('/blocks/homework/assets/bootstrap/css/bootstrap.css');

echo $OUTPUT->header();

// Show mode switching tabs
echo $activityCenter->display->modeTabs($mode);

echo $activityCenter->display->showTabs($mode, $view);

include "./views/{$mode}/{$view}.php"; //FIXME: don't leave this like this

echo $OUTPUT->footer();
