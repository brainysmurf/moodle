<?php
require_once('../../config.php');
require_once('../../enrol/locallib.php');

$activity_id = required_param('activity_id', PARAM_RAW);
$new_name = optional_param('new_name', '', PARAM_RAW);
$enrol = optional_param('enrol', '', PARAM_RAW);
echo "HEY";

if ($enrol == "ENROL") {
    foreach ($SESSION->dnet_activity_center_individuals as $individual) {
        $user = $DB->get_record("user", array("idnumber"=>$individual));
        if (!$user) {
            // Could get here if something strange happened ...
            continue;
        }
        // enrol
    }

} else if ($enrol == "DEENROL") {
    $user_id = required_param('user_id', PARAM_RAW);
    $activity_id = required_param('activity_id', PARAM_RAW);

    if( !$context = get_context_instance(CONTEXT_COURSE, $activity_id, MUST_EXIST) ) {
        //return "-1 Could not get context for course ".$short_name." with ID ".$activity_id;
    }

    $course = $DB->get_record('course', array('id'=>$activity_id));
    $user = $DB->get_record('user', array('id'=>$user_id));

    $selfenrolment_plugin = enrol_get_plugin('self');
    $enrolment_instances = enrol_get_instances($activity_id, true);
    foreach ($enrolment_instances as $instance) {
        if ($instance->enrol == 'self') {
            $selfenrolment_plugin->unenrol_user($instance, $user->id);
            echo 'unenrolled';
        }
    }
}

if (!empty($new_name)) {
    $DB->update_record('course', array(
        "id"=>$activity_id,
        "fullname"=>$new_name
        ));
}
