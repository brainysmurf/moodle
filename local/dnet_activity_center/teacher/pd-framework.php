<?php

/**
 * Display all activities so a teacher can pick which ones they want to manage
 */

include '../roles/common_top.php';

echo $activityCenter->display->showTabs('teacher', 'pdframework');

echo $OUTPUT->sign('ok-sign', 'Choose PD Strand', 'Explanation.');

echo $activityCenter->display->displayPDFrameworkChoices($activityCenter->userID());

include '../roles/common_end.php';
