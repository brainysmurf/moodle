<?php

/**
 * Display all activities so a teacher can pick which ones they want to manage
 */

include '../roles/common_top.php';

$activityCenter->setCurrentMode('admin');

echo $activityCenter->display->showTabs('admin', 'summary-sec');

echo $OUTPUT->sign('rocket', 'Secondary Activty Report', 'Lists what everyone chose for their PD and Activities');

$info = $activityCenter->data->getUsersSummary('teachersSEC');
echo $activityCenter->display->summaryList($info);

include '../roles/common_end.php';
