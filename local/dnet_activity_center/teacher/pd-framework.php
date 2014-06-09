<?php

/**
 * Allow teachers to sign up for PD
 */

include '../roles/common_top.php';

$activityCenter->setCurrentMode('teacher');

echo $activityCenter->display->showTabs('teacher', 'pdframework');

echo $OUTPUT->sign('ok-sign', 'Choose PD Strand', 'Explanation.');

echo $activityCenter->display->displayPDFrameworkChoices($activityCenter->getUserID());

include '../roles/common_end.php';
