<?php

// Loops through selected activities
// and sets the visible flag as appropriate
// Doesn't actually 'toggle' despite the name

$on_or_off = optional_param('on_or_off', '', PARAM_RAW);

if (!empty($on_or_off)) {
    $value = $on_or_off == "ON" ? 1 : 0;

    foreach ($SESSION->dnet_activity_center_activities as $activity_id) {
        $course = $DB->get_record('course', array('id'=>$activity_id));
        $DB->update_record('course', array(
                'id'=>$activity_id,
                'visible'=>$value
            ));
    }

    sign('thumbs-up', "Done.", "All the selected activities have been changed to ".$on_or_off.'.');

} else {

    sign('info-sign', 'Make all selected activities visible or invisible.',
        'Use the buttons below to complete the action.');

    $buttons = '
    <ul class="buttons">
    <a class="btn" href="?mode='.TOGGLEVISIBILITY.'&on_or_off=ON"><i class="icon-check"></i> Make all selected activities visible</a>
    <br />
    <a class="btn" href="?mode='.TOGGLEVISIBILITY.'&on_or_off=OFF"><i class="icon-check-empty"></i> Make all selected activities invisible</a>
    </ul>
    ';
    echo $buttons;

}
