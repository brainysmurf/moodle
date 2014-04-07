<?php
require_once($CFG->libdir.'/enrollib.php');
set_time_limit(1800);  // half an hour...

$confirm = optional_param('confirm', '', PARAM_RAW);
if ($confirm == "YES") {

    // step through each activity, and unenroll all the non-managers and non-editors
    // special case for students who are also enrolled as editors, what to do?
    $participant_role = 5;
    $selfenrolment = enrol_get_plugin('self');

    $sql = '
SELECT
    usr.id as user_id,
    crs.id, crs.fullname AS course_name,
    concat(usr.firstname, \' \', usr.lastname) as user_fullname,
    rle.name as role_name
FROM
    {role_assignments} ra
JOIN
    {context} ctx ON ra.contextid = ctx.id
JOIN
    {course} crs on ctx.instanceid = crs.id
JOIN
    {user} usr on ra.userid = usr.id
JOIN
    {role} rle on ra.roleid = rle.id
WHERE
   crs.id = ?';

    foreach ($SESSION->dnet_activity_center_activities as $activity_id) {
        $enrolment_instances = enrol_get_instances($activity_id, true);
        foreach ($enrolment_instances as $instance) {
            if ($instance->enrol == 'self') {
                $params = array($activity_id);
                $rows = $DB->get_records_sql($sql, $params);
                foreach ($rows as $row) {
                    if ($row->role_name == "Student") {
                        // Parents automatically get unenrolled through the self unenrollment feature
                        $selfenrolment->unenrol_user($instance, $row->user_id);
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
