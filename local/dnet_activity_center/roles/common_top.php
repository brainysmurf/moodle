<?php

require_once('../../../config.php');
require_once('../portables.php');
require_once('../output.php');
require_once '../../../local/dnet_common/sharedlib.php';

$mode = optional_param('mode', '', PARAM_RAW);

setup_page();
