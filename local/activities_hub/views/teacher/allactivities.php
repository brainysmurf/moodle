<?php

echo $OUTPUT->sign('rocket', 'All Activities', 'This page shows all the activities available. Click on an Activity you would like to supervise.');

$activities = $activityCenter->data->getActivities();
echo $activityCenter->display->activityList($activities, false, 'becomeActivityManagerList');

