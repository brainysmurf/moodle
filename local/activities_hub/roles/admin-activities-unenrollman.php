<?php
require_once($CFG->libdir.'/enrollib.php');
set_time_limit(1800);  // half an hour...

$confirm = optional_param('confirm', '', PARAM_RAW);
if ($confirm == "YES") {

    // step through each activity, and unenroll all the non-managers and non-editors
    // special case for students who are also enrolled as editors, what to do?

    $selfenrolment = enrol_get_plugin('manual');
    foreach ($SESSION->dnet_activity_center_activities as $activity_id) {
        $context = get_context_instance(CONTEXT_COURSE, $activity_id, true);
        foreach(array(MANAGER_ROLE_ID) as $role_id) {    # not TEACHER_ROLE_ID because those are students with editing privledges
            $users = get_role_users($role_id, $context);
            $enrolment_instances = enrol_get_instances($activity_id, true);

            foreach ($enrolment_instances as $instance) {
                if ($instance->enrol == 'manual') {
                    foreach ($users as $user) {
                        $selfenrolment->unenrol_user($instance, $user->id);
                    }
                }
            }
        }
    }

    sign('thumbs-up', 'Done', 'All "teachers" have been cleared from the select activities.');
    //redirect(derive_plugin_path_from(''));
} else {
    sign('info-sign', 'Un-enrol all teachers (managers) from all these courses', 'Are you sure you want to do that? <a class="btn" href="?mode='.UNENROLLMAN.'&confirm=YES">Yes!</a>');
}
