<?php
require_once('../../../config.php');

$text = required_param('text', PARAM_RAW);
$userid = required_param('userid', PARAM_RAW);

// $data = json_encode(array(
//         'season'=>$season
//     ));

$field = $DB->get_record('user_info_field', array('shortname'=>'goal20145'), '*', MUST_EXIST);
$exists = $DB->get_record('user_info_data', array(
    'userid'=>$userid,
    'fieldid'=>$field->id
));

var_dump($text);

if (!$exists) {
    $DB->insert_record('user_info_data', array(
        'userid'=>$userid,
        'fieldid'=>$field->id,
        'data'=>$text
    ));
} else {
    $DB->update_record('user_info_data', array(
        'id'=>$exists->id,
        'data'=>$text
        )
    );
}

