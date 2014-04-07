<?php

require_once('../../../config.php');

function get_user_activity_enrollments($idnumber) {
    global $DB;

    $sql = "
    select
        crs.fullname, usr.idnumber
    from
        {enrol} enrl
    join
        {user_enrolments} usrenrl
            on usrenrl.enrolid = enrl.id
    join
        {course} crs
            on enrl.courseid = crs.id
    join
        {user} usr
            on usrenrl.userid = usr.id
    where
        enrl.enrol = ? and
        usr.idnumber = ?
    order by
        crs.fullname";

    $params = array("self", $idnumber);
    return $DB->get_records_sql($sql, $params);
}
