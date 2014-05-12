<?php

include '../roles/common_top.php';

echo $activityCenter->display->showTabs('teacher', 'all');

echo $OUTPUT->sign('plus-sign', 'Suggest A New Activity', 'Do you want to offer an activity that is not currently available? Use this form to suggest it.');

echo '<p>To do.</p>';

include '../roles/common_end.php';
