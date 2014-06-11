<?php

echo $OUTPUT->sign('ok-sign', 'Your Activities', 'These are the activities you currently supervise.');

$managedActivities = $activityCenter->data->getActivitiesManaged(false, $activityCenter->getUserID());

echo $activityCenter->display->activityList($managedActivities);
