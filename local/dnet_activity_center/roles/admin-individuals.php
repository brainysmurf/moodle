<?php

$powerschool = optional_param('powerschool', '', PARAM_RAW);

switch ($mode) {

    case BROWSE:
        output_forms(PLACEHOLDER, "students", BROWSE);
        if (!empty($powerschool)) {
            $where = "idnumber like ?";
            $familyid = substr($powerschool, 0, 4);
            $params = array($familyid.'%');
            $users = $DB->get_records_select("user", $where, $params);
            if (empty($users)) {
                sign("thumbs-down", "No such users", "Could not find anything!");
                death("");
            }
            foreach ($users as $user) {
                user_box($user);
            }
        }
        break;

    case CLEAR:
        unset($SESSION->dnet_activity_center_individuals);
        sign("thumbs-up", "List cleared.", "Go to ".SELECT." to start building a new list.");
        break;

    case SELECT:
        include 'admin-individuals-select.php';
        break;

    case ENROL:
        include 'admin-individuals-enrol.php';
        break;

}
