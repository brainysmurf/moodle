<?php

include '../roles/common_top.php';

echo $activityCenter->display->showTabs('teacher', 'goals');
echo $OUTPUT->sign('plus-sign', 'Enter your goals', 'Explanation');

$goal = $activityCenter->data->getUserGoal($activityCenter->userid());
echo $activityCenter->display->displayEnterComment($activityCenter->userID(), $goal);

include '../roles/common_end.php';
