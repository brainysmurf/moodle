<?php

/**
 * Allows teachers to add/edit their desired goals
 */

include '../roles/common_top.php';

$activityCenter->setCurrentMode('teacher');

echo $activityCenter->display->showTabs('teacher', 'goals');
echo $OUTPUT->sign('plus-sign', 'Enter your goals', 'Explanation');

$goal = $activityCenter->data->getUserGoal($activityCenter->getUserID());
echo $activityCenter->display->displayEnterComment($activityCenter->getUserID(), $goal);

include '../roles/common_end.php';
