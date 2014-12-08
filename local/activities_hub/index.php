<?php
require_once '../../config.php';
require_once 'portables.php';
require_once '../../local/dnet_common/sharedlib.php';
require_once '../../cohort/lib.php';

require_login();

function as_teacher() {
		redirect(derive_plugin_path_from('roles/teachers.php') . '?' . http_build_query($_GET));
}

if (is_admin() or is_activities_head()) {

	if (isset($SESSION->dnet_activity_center_submode) && $SESSION->dnet_activity_center_submode == "becometeacher") {
		as_teacher();
	} else {
		redirect(derive_plugin_path_from('roles/admin.php')  . '?' . http_build_query($_GET));
	}

} else if (is_teacher()) {

	redirect('teacher/index.php');

}  else if (is_student()) {

	redirect('student/index.php');

}  else if (is_parent()) {


}
