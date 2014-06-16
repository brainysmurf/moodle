<?php
require_once($CFG->libdir.'/enrollib.php');


function add_activity_to_sesssion($activity_id) {
    global $SESSION;
    if (empty($SESSION->dnet_activity_center_activities)) {
        $SESSION->dnet_activity_center_activities = array();
    }

    if (!in_array($activity_id, $SESSION->dnet_activity_center_activities)) {
        $SESSION->dnet_activity_center_activities[] = $activity_id;
    }
}

switch ($mode) {
    case START_AGAIN:
        $SESSION->dnet_activity_center_submode = '';
        redirect(derive_plugin_path_from('index.php'));
        break;

    case CLEAR:
        unset($SESSION->dnet_activity_center_activities);
        sign("thumbs-up", "List cleared.", "Go to ".SELECT." to start building a new list.");
        break;

    case TOGGLEENROLLMENTS:
        include 'admin-activities-enrollments.php';
        break;

    case TOGGLEVISIBILITY:
        include 'admin-activities-visibility.php';
        break;

    case UNENROLLALL:
        include 'admin-activities-unenrollall.php';
        break;

    case UNENROLLMAN:
        include 'admin-activities-unenrollman.php';
        break;

    case SELECT:
        include 'admin-activities-select.php';
        break;
}
