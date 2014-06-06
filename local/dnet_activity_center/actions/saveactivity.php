<?php

/**
 * Saves changes to an activity / creates a new activity
 */

require '../../config.php';

$action = optional_param('action', 'add', PARAM_RAW); // add or update

