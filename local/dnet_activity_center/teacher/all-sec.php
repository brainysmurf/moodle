<?php

/**
 * Display all activities so a teacher can pick which ones they want to manage
 */

include '../roles/common_top.php';

$activityCenter->setCurrentMode('teacher');

echo $activityCenter->display->showTabs('teacher', 'all-sec');

echo $OUTPUT->sign('rocket', 'All Secondary Activities', 'This page shows all the activities available. Click on an Activity you would like to supervise.');

$activities = $activityCenter->data->getActivities(false, false, $path='/1/117');
echo $activityCenter->display->activityList($activities, false, 'becomeActivityManagerList');

include '../roles/common_end.php';
