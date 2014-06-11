<?php

/**
 * Save a teacher's goals in the database
 */

require_once('../../../config.php');

$department = required_param('department', PARAM_RAW);
$individual = required_param('individual', PARAM_RAW);
$pastleadership = required_param('pastleadership', PARAM_RAW);
$additional = required_param('additional', PARAM_RAW);
$userid = required_param('userid', PARAM_RAW);

$data = json_encode(array(
	'department' => $department,
	'individual' => $individual,
	'pastleadership' => $pastleadership,
	'additional' => $additional
));

$field = $DB->get_record('user_info_field', array('shortname'=>'goal20145'), '*', MUST_EXIST);
$exists = $DB->get_record('user_info_data', array(
    'userid'=>$userid,
    'fieldid'=>$field->id
));

if (!$exists) {
    $DB->insert_record('user_info_data', array(
        'userid'=>$userid,
        'fieldid'=>$field->id,
        'data'=>$data
    ));
} else {
    $DB->update_record('user_info_data', array(
        'id'=>$exists->id,
        'data'=>$data
        )
    );
}

