<?php

/**
 * Show form for creating a new activity
 */

echo $OUTPUT->sign('plus-sign', 'Create A New Activity', 'Description.');

define('FORMACTION', 'add');

require dirname(dirname(__DIR__)) . '/include/newactivityform.php';


