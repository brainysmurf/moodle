<?php
require_once '../../config.php';
require_once 'lib.php';

if (isloggedin()) {
    require_login();
    if (is_admin($USER->id)) {
        redirect(derive_plugin_path_from('roles/secretaries.php'));
    } else if (is_teacher($USER->id)) {
        redirect(derive_plugin_path_from('roles/teacher.php'));
    }  else if (is_student($USER->id)) {
        redirect(derive_plugin_path_from('roles/student.php'));
    }
} else {
    redirect(derive_plugin_path_from('roles/guest.php'));
}
