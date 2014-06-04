<?php

/**
 * Display all activities so a teacher can pick which ones they want to manage
 */

include '../roles/common_top.php';

echo $activityCenter->display->showTabs('teacher', 'allpd');

echo $OUTPUT->sign('rocket', 'Pick PD sessions', 'In future you will be able to sign up for specific PD sessions here.');

// $activities = $activityCenter->data->getPDs();
// echo $activityCenter->display->activityList($activities, false, 'becomeActivityManagerList');

include '../roles/common_end.php';
