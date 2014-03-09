<?php

require_once '../../../config.php';
require_once '../lib.php';
require_once '../output.php';

require_login();

setup_activity_center_page();
output_tabs('Teacher');

if (!is_teacher($USER->id)) {
    echo 'Only teacher accounts can access this section. Contact the DragonNet administrator if you think you should have access.';
    die();
}
