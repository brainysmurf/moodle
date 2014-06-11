<?php

/**
 * Display all activities so a teacher can pick which ones they want to manage
 */

require_login();

include '../roles/common_top.php';

$activityCenter->setCurrentMode('teacher');

echo $activityCenter->display->showTabs('teacher', 'overview');

echo $OUTPUT->sign('ok-sign', 'Activity & PD Overview', 'These are the activities you currently supervise and the PD you have signed up for.');

$pdoutput = $activityCenter->data->getUserPDSelection($activityCenter->getUserID());
$goal = $activityCenter->data->getUserGoal($activityCenter->getUserID());

$managedActivities = $activityCenter->data->getActivitiesManaged(false, $activityCenter->getUserID());

// echo $activityCenter->display->displayPDFramework($pdoutput);
echo $activityCenter->display->overview($goal, $managedActivities, $pdoutput);

include '../roles/common_end.php';
