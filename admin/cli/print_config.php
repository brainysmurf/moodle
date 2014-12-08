<?php

/**
 * Useful for debugging
 */

define('CLI_SCRIPT', true); // Very important to have this here!

require(dirname(dirname(dirname(__FILE__))).'/config.php');

print_r($CFG);
