<?php
require_once('../../config.php');

require_login();

$userid = required_param('userid', PARAM_RAW);
$change_username_to = optional_param('change_username_to', '', PARAM_RAW);
$change_firstname_to = optional_param('change_firstname_to', '', PARAM_RAW);
$change_lastname_to = optional_param('change_lastname_to', '', PARAM_RAW);

if (!empty($change_username_to)) {
    // First, determine if this is an account where the last name is the same as the email address
    // This is a legacy thing, might as well keep the system consistent
    // There is no real reason for making the lastname the same as the email address, but because powerschool doesn't have
    // mother or father's names (when I designed dragonnet originally) I had to make due

    $user = $DB->get_record('user', array("id"=>$userid));
    if ($user->lastname == $user->email) {
        $also_change_lastname = true;
    }

    // This is the main update here:

    $DB->update_record('user', array(
        "id"=>$userid,
        "email"=>$change_username_to,
        "username"=>$change_username_to
        ));

    // Now also update the lastname if we have an account as described above.

    if ($also_change_lastname) {
        $DB->update_record('user', array(
            "id"=>$userid,
            "lastname"=>$change_username_to
        ));
    }

    echo 'worked!';
}


if (!empty($change_firstname_to)) {
    // First, determine if this is an account where the last name is the same as the email address
    // This is a legacy thing, might as well keep the system consistent
    // There is no real reason for making the lastname the same as the email address, but because powerschool doesn't have
    // mother or father's names (when I designed dragonnet originally) I had to make due

    $DB->update_record('user', array(
        "id"=>$userid,
        "firstname"=>$change_firstname_to
        ));
}

if (!empty($change_lastname_to)) {
    // First, determine if this is an account where the last name is the same as the email address
    // This is a legacy thing, might as well keep the system consistent
    // There is no real reason for making the lastname the same as the email address, but because powerschool doesn't have
    // mother or father's names (when I designed dragonnet originally) I had to make due

    $DB->update_record('user', array(
        "id"=>$userid,
        "lastname"=>$change_lastname_to
        ));
}
