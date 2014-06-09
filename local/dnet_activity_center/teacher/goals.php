<?php

/**
 * Allows teachers to add/edit their desired goals
 */

include '../roles/common_top.php';

$activityCenter->setCurrentMode('teacher');

echo $activityCenter->display->showTabs('teacher', 'goals');
echo $OUTPUT->sign('plus-sign', 'Enter your goals', 'Before entering your goals, please read through the Goal Setting Guidance (linked) and complete the Individual Goal Setting template (linked).  Your Head of Department/Subject Leader will guide this process.  Please paste your goals in the fields below.  Please paste only text, no tables etc.  Thank you.');

$goal = $activityCenter->data->getUserGoal($activityCenter->getUserID());
echo $activityCenter->display->displayEnterComment($activityCenter->getUserID(), $goal);

include '../roles/common_end.php';
