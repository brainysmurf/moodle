<?php

/**
 * Display all activities so a teacher can pick which ones they want to manage
 */

include '../roles/common_top.php';

echo $activityCenter->display->showTabs('teacher', 'myactivities');

echo $OUTPUT->sign('ok-sign', 'Your Activities', 'These are the activities you currently supervise.');

$managedActivities = $activityCenter->data->getActivitiesManaged(false, $activityCenter->userID());

echo $activityCenter->display->activityList($managedActivities);

include '../roles/common_end.php';
