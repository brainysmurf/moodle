<?php

/**
 * This script will enrol every student as a 'Teacher' in
 * the course that has the idnumber "OLP:[IDNUMBER]" if the
 * course exists
 */

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(__FILE__))).'/config.php');

require_once($CFG->dirroot . '/lib/ssisolp.php');
$OLPManager = new OLPManager();
$OLPManager->run();
