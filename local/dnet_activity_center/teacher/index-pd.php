<?php

/**
 * Display all activities so a teacher can pick which ones they want to manage
 */

include '../roles/common_top.php';

echo $activityCenter->display->showTabs('teacher', 'pdframework');

echo $OUTPUT->sign('ok-sign', 'Choose PD Framework', 'Explanation.');

#$managedActivities = $activityCenter->data->getActivitiesManaged(false, $activityCenter->userID());
$signedupPD = $activityCenter->data->getPDSignedUp(false, $activityCenter->userID());

echo $activityCenter->display->activityList($signedupPD);

include '../roles/common_end.php';
