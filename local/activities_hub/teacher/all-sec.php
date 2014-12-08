<?php

/**
 * Display all activities so a teacher can pick which ones they want to manage
 */

include '../roles/common_top.php';

$activityCenter->setCurrentMode('teacher');

echo $activityCenter->display->showTabs('teacher', 'all-sec');

echo $OUTPUT->sign('rocket', 'All Secondary Activities', 'This page shows all the activities currently available for selection. Click on an Activity you would like to supervise. <br /><strong class="red">Red</strong> means that activity already has enough supervisors, <strong class="green">green</strong> means there are spaces available. <strong class="blue">White/Blue</strong> means that you are listed as supervising it.');

$activities = $activityCenter->data->getActivities(false, false, $path='/1/117');
echo $activityCenter->display->activityList($activities, false, 'becomeActivityManagerList');

include '../roles/common_end.php';
