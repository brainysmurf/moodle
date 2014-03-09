<?php

$userid = isset($_GET['userid']) ? $_GET['userid'] : FALSE;

require_once '../../config.php';
require_login();

if ($userid) {
    global $DB;
    $result = $DB->get_record('user_activity_bus', array('userid'=>$userid));
    $user = $DB->get_record('user', array('id'=>$userid));
    $now = $result->bus;
    $new = (int)(!((bool)$now));

    $result->bus = $new;
    $result->idnumber = $user->idnumber;
    $result->name = $user->firstname.' '.$user->lastname;

    $DB->update_record('user_activity_bus', $result);

    echo json_encode($result);
}
?>
