<?php

/**
 * Show form for creating a new activity
 */

echo $OUTPUT->sign('plus-sign', 'Create A New Activity', 'Do you want to offer an activity that is not currently available? Use this form to suggest it.');

require dirname(dirname(__DIR__)) . '/include/newactivityform.php';
