<?php

/**
 * Runs the Destiny import now regardless of when it was last run
 */

define('CLI_SCRIPT', true);

require dirname(dirname(__DIR__)) . '/config.php';
require __DIR__ . '/lib.php';

local_dnet_syncing_run_destiny_import();
