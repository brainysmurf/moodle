<?php

/**
 * Display all activities so a teacher can pick which ones they want to manage
 */

include '../roles/common_top.php';

$activityCenter->setCurrentMode('teacher');

echo $activityCenter->display->showTabs('teacher', 'summary-sec');

echo $OUTPUT->sign('rocket', 'Secondary reports', 'This page shows all the activities currently available for selection. Click on an Activity you would like to supervise. <br /><strong class="red">Red</strong> means that activity already has enough supervisors, <strong class="green">green</strong> means there are spaces available. <strong class="blue">White/Blue</strong> means that you are listed as supervising it.');

$info = $activityCenter->data->getUsersSummary('teachersSEC');
echo $activityCenter->display->summaryList($info);

include '../roles/common_end.php';
