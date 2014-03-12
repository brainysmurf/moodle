<?php
defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__DIR__)) . '/cohort/lib.php');

// definitions
// TODO: Use session data instead of these manual lookups
// Add $user->is_activities_head as well ?

function setup_account_management_page() {
    global $PAGE;
    global $OUTPUT;

    $PAGE->set_context(context_system::instance());
    $PAGE->set_url(derive_plugin_path_from('index.php'));
    $PAGE->set_title("Reset DragonNet Passwords");
    $PAGE->set_heading("Reset DragonNet Passwords");

    echo $OUTPUT->header();
}

function is_admin($userid) {
    if (has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM))) {
        return true;
    }
}

function is_teacher($userid) {
    global $SESSION;
    global $USER;
    if (is_admin($USER->id)) {
        return true;
    }
    if ($SESSION->is_teacher) {
        return true;
    }
    return false;
}

function is_student($userid) {
    global $SESSION;
    global $USER;
    if (is_admin($USER->id)) {
        return true;
    }
    if ($SESSION->is_student) {
        return true;
    }
    return false;
}

function get_user_activity_enrollments($userid) {
    global $DB;
    return $DB->get_recordset_sql("
select
    crs.id as course_id,
    enrl.id as enrolid,
    usr.idnumber as idnumber,
    concat(usr.firstname, ' ', usr.lastname) as username,
    regexp_replace(crs.fullname, '\(.*\)', '') as fullname
from ssismdl_enrol enrl
    join ssismdl_user_enrolments usrenrl
        on usrenrl.enrolid = enrl.id
    join ssismdl_course crs
        on enrl.courseid = crs.id
    join ssismdl_course_categories cat
        on crs.category = cat.id
    join ssismdl_user usr
        on usrenrl.userid = usr.id
where
    crs.visible = 1 and
    usr.id = ".$userid." and
    cat.path like '/1/%' and
    enrl.enrol = 'self'");
}

function get_family_activity_enrollments($familyid) {
    global $DB;
    return $DB->get_recordset_sql("
select
    crs.id as course_id,
    usr.idnumber as idnumber,
    concat(usr.firstname, ' ', usr.lastname) as username,
    regexp_replace(crs.fullname, '\(.*\)', '') as fullname
from ssismdl_enrol enrl
    join ssismdl_user_enrolments usrenrl
        on usrenrl.enrolid = enrl.id
    join ssismdl_course crs
        on enrl.courseid = crs.id
    join ssismdl_course_categories cat
        on crs.category = cat.id
    join ssismdl_user usr
        on usrenrl.userid = usr.id
where
    crs.visible = 1 and
    usr.idnumber like '".$familyid."%' and
    cat.path like '/1/%' and
    enrl.enrol = 'self'");
}

function derive_plugin_path_from($stem) {
    // Moodle really really should be providing a standard way to do this
    return "/local/dnet_reset_passwords/{$stem}";
}

/**
 * method masks the username of an email address
 *
 * @param string $email the email address to mask
 * @param string $mask_char the character to use to mask with
 * @param int $percent the percent of the username to mask
 */
function mask_email( $email, $mask_char='*', $percent=50 )
{

        list( $user, $domain ) = preg_split("/@/", $email );

        $len = strlen( $user );

        $mask_count = floor( $len * $percent /100 );

        $offset = floor( ( $len - $mask_count ) / 2 );

        $masked = substr( $user, 0, $offset )
                .str_repeat( $mask_char, $mask_count )
                .substr( $user, $mask_count+$offset );


        return( $masked.'@'.$domain );
}

