<?php

// Loops through selected activities
// and sets the visible flag as appropriate
// Doesn't actually 'toggle' despite the name

$on_or_off = optional_param('on_or_off', '', PARAM_RAW);

if (!empty($on_or_off)) {
    $value = $on_or_off == "ON" ? 1 : 0;
    $selfenrolment_plugin = enrol_get_plugin('self');

    foreach ($SESSION->dnet_activity_center_activities as $activity_id) {
        $enrolment_instances = enrol_get_instances($activity_id, true);
        foreach ($enrolment_instances as $instance) {
            if ($instance->enrol == 'self') {
                $DB->update_record('enrol', array(
                    'id'=>$instance->id,
                    'customint6'=>$value
                    ));
            }
        }
    }

    sign('thumbs-up', "Done.", "All the selected activities have been changed.");

} else {

    sign('info-sign', 'Enable enrollments for selected activities.',
        'Use the buttons below to complete the action.');

    $buttons = '
    <ul class="buttons">
    <a class="btn" href="?mode='.TOGGLEENROLLMENTS.'&on_or_off=ON"><i class="icon-check"></i> Turn on enrollments for selected activities</a>
    <br />
    <a class="btn" href="?mode='.TOGGLEENROLLMENTS.'&on_or_off=OFF"><i class="icon-check-empty"></i> Turn off enrollments for selected activities</a>
    </ul>
    ';
    echo $buttons;

}
