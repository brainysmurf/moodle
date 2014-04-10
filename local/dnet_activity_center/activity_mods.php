<?php
require_once('../../config.php');

$activity_id = required_param('activity_id', PARAM_RAW);
$new_name = optional_param('new_name', '', PARAM_RAW);

if (!empty($new_name)) {
    $DB->update_record('course', array(
        "id"=>$activity_id,
        "fullname"=>$new_name
        ));

    echo 'worked!';
}
