<?php

/**
 * Show form for creating a new activity
 */

echo $OUTPUT->sign('plus-sign', 'Create A New Activity', 'This page allows you to create a new activity offered to students.');

define('FORMACTION', 'add');

require dirname(dirname(__DIR__)) . '/include/newactivityform.php';


