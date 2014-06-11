<?php

/**
 * Allows teachers to add/edit their desired goals
 */

include '../roles/common_top.php';

$activityCenter->setCurrentMode('teacher');

echo $activityCenter->display->showTabs('teacher', 'goals');
echo $OUTPUT->sign('plus-sign', 'Enter your goals', 'Before entering your goals, please read through the <strong><a href="https://dragonnet.ssis-suzhou.net/pluginfile.php/74998/mod_resource/content/0/Goal%20Setting%20Guidance%202014-15.pdf">Goal Setting Guidance</a></strong> and complete the <strong><a href="https://dragonnet.ssis-suzhou.net/mod/resource/view.php?id=56997">Individual Goal Setting Template</a></strong>.  Your Head of Department/Subject Leader will guide this process.  Please paste your goals in the fields below.  Please paste only text, no tables etc.  Thank you.');

$goal = $activityCenter->data->getUserGoal($activityCenter->getUserID());
echo $activityCenter->display->displayEnterComment($activityCenter->getUserID(), $goal);

include '../roles/common_end.php';
