<?php
require_once '../../config.php';
require_once '../../local/dnet_common/sharedlib.php';
require_once 'portables.php';

if (isloggedin()) {
    if (is_teacher() or is_secretary()) {
        redirect(derive_plugin_path_from('home.php'));
    }  else {
        die("Only teachers and secretaries have access to this area.");
    }
} else {
    die("You must be logged into DragonNet.");
}
