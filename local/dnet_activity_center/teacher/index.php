<?php

/**
 * Display all activities so a teacher can pick which ones they want to manage
 */

include '../roles/common_top.php';

echo $activityCenter->display->showTabs('teacher', 'overview');

echo $OUTPUT->sign('ok-sign', 'Activity & PD Overview', 'These are the activities you currently supervise and the PD you have signed up for.');

$pdoutput = $activityCenter->data->getUserPDSelection($activityCenter->userID());
$managedActivities = $activityCenter->data->getActivitiesManaged(false, $activityCenter->userID());

// echo $activityCenter->display->displayPDFramework($pdoutput);
echo $activityCenter->display->overview($managedActivities, $pdoutput);

include '../roles/common_end.php';
