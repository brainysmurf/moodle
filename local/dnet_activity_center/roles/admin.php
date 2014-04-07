<?php

include 'common_top.php';
include 'tabs_admin.php';

if (empty($mode)) {
    $mode = SELECT;
}

if (isset($SESSION->dnet_activity_center_submode)) {
    $sub_mode = $SESSION->dnet_activity_center_submode;
} else {
    $sub_mode = '';
}

if ($sub_mode == strtolower(SUBMODE_INDIVIDUALS)) {

    output_tabs($mode, array(BROWSE, SELECT, ENROL, DEENROL, START_AGAIN));
    include 'admin-individuals.php';

} else if ($sub_mode == strtolower(SUBMODE_ACTIVITIES)) {

    output_tabs($mode, array(BROWSE, SELECT, ORGANIZE, CLEAR, UNENROLLALL, NEW_, START_AGAIN));
    include 'admin-activities.php';

} else {

    sign("question-sign", "Which mode?", "Are you making changes to activities, or to individuals? Or do you need to access this area as a regular teacher?");
    output_submode_choice("", array(SUBMODE_ACTIVITIES, SUBMODE_INDIVIDUALS, BECOME_TEACHER));

}


include 'common_end.php';
