<?php
require_once '../../config.php';
require_once 'lib.php';

require_login();

if (is_admin($USER->id)) {
    redirect(derive_plugin_path_from('roles/coordinator.php'));
} else if (is_parent($USER->id)) {
    redirect(derive_plugin_path_from('roles/parent.php'));
} else if (is_teacher($USER->id)) {
    redirect(derive_plugin_path_from('roles/teacher.php'));
}  else if (is_student($USER->id)) {
    redirect(derive_plugin_path_from('roles/student.php'));
}
