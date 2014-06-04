<?php
require_once('../../../config.php');

$category = required_param('category', PARAM_RAW);
$implementation = required_param('implementation', PARAM_RAW);
$userid = required_param('userid', PARAM_INT);

$pattern = '/^\((.*?)\)/';  # start of string has a parens
$season = preg_match($pattern, $category, $matches);
if (!empty($season)) {
    $data = json_encode(array(
            'season'=>$matches[1],
            'strand'=>trim(preg_split($pattern, $category)[1]),
            'choice'=>$implementation
    ));
} else {
    $data = json_encode(array('category'=>'', 'season'=>'', 'implementation'=>''));
}

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

echo 'yo';
