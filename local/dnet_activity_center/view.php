<?php

/**
 * Displays a view from the views directory
 */

require '../../config.php';

require './ActivityCenter/ActivityCenter.php';
$activityCenter = new \SSIS\ActivityCenter\ActivityCenter();

$view = optional_param('view', false, PARAM_RAW);


if (!$view) {
	$view = $view ? $view : $activityCenter->defaultViewForMode($mode);
	redirect("view.php?view={$view}");
	exit();
}

$activityCenter->mode = $mode;

$PAGE->set_title('Activity Center');
$PAGE->set_heading('Activity Center');

$PAGE->requires->css('/blocks/homework/assets/bootstrap/css/bootstrap.css');

echo $OUTPUT->header();

// Show mode switching tabs
echo $activityCenter->display->showTabs($mode, $view);

include "./views/{$mode}/{$view}.php"; //FIXME: don't leave this like this

echo $OUTPUT->footer();
