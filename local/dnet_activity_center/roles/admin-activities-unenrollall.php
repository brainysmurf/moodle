<?php
require_once($CFG->libdir.'/enrollib.php');
set_time_limit(1800);  // half an hour...

$confirm = optional_param('confirm', '', PARAM_RAW);
if ($confirm == "YES") {

    // step through each activity, and unenroll all the non-managers and non-editors
    // special case for students who are also enrolled as editors, what to do?

    $selfenrolment = enrol_get_plugin('self');
    foreach ($SESSION->dnet_activity_center_activities as $activity_id) {
        $context = get_context_instance(CONTEXT_COURSE, $activity_id, true);
        foreach(array(STUDENT_ROLE_ID, PARENT_ROLE_ID) as $role_id) {
            $users = get_role_users($role_id, $context);
            $enrolment_instances = enrol_get_instances($activity_id, true);

            foreach ($enrolment_instances as $instance) {
                if ($instance->enrol == 'self') {
                    foreach ($users as $user) {
                        $selfenrolment->unenrol_user($instance, $user->id);
                    }
                }
            }
        }
    }

    sign('thumbs-up', 'Done', 'All "participants" (including students & parents) have been cleared from the select activities.');
    //redirect(derive_plugin_path_from(''));
} else {
    sign('info-sign', 'Un-enrol all participants from all these courses', 'Are you sure you want to do that? <a class="btn" href="?mode='.UNENROLLALL.'&confirm=YES">Yes!</a>');
}
