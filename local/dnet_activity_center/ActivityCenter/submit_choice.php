<?php
require_once('../../../config.php');

$category = required_param('category', PARAM_RAW);
$implementation = required_param('implementation', PARAM_RAW);
$userid = required_param('userid', PARAM_RAW);

echo 'hi';
$season = preg_match('^\(.*?)', $category);
var_dump($season);

// $data = json_encode(array(
//         'season'=>$season
//     ));

$field = $DB->get_record('user_info_field', array('shortname'=>'pdchoices20145'), '*', MUST_EXIST);
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

echo 'hey';
