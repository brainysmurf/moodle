<?php
require_once '../../config.php';
require_once 'portables.php';
require_once '../../local/ssiscommon/lib.php';

if (isloggedin()) {
    if (is_secretary()) {
        redirect(derive_plugin_path_from('roles/secretaries.php'));
    } else if (is_teacher()) {
        redirect(derive_plugin_path_from('roles/teacher.php'));
    }  else if (is_student()) {
        redirect(derive_plugin_path_from('roles/student.php'));
    }
} else {
    redirect(derive_plugin_path_from('roles/parent.php'));
}
