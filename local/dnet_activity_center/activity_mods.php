<?php
require_once('../../config.php');

$activity_id = required_param('activity_id', PARAM_RAW);
$new_name = optional_param('new_name', '', PARAM_RAW);
$enrol = optional_param('enrol', '', PARAM_RAW);

if ($enrol == "ENROL") {
    foreach ($SESSION->dnet_activity_center_individuals as $individual) {
        $user = $DB->get_record("user", array("idnumber"=>$individual));
        if (!$user) {
            // Could get here if something strange happened ...
            continue;
        }
        // enrol
    }
}

if (!empty($new_name)) {
    $DB->update_record('course', array(
        "id"=>$activity_id,
        "fullname"=>$new_name
        ));
}
