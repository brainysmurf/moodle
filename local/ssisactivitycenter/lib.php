<?php
defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__DIR__)) . '/cohort/lib.php');

// definitions
// TODO: Use session data instead of these manual lookups
// Add $user->is_activities_head as well ?

function setup_activity_center_page() {
    global $PAGE;
    global $OUTPUT;
    $PAGE->set_context(context_system::instance());
    $PAGE->set_url(derive_plugin_path_from('index.php'));
    $PAGE->set_title("SSIS Activity Center");
    $PAGE->set_heading("SSIS Activity Center");

    echo $OUTPUT->header();
}

// // function is_admin($userid) {
// //     if (has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM))) {
// //         return true;
// //     }
// //     return cohort_is_member(ACTIVITIES_COHORT_ID, $userid);
// // }

// // function is_parent($userid) {
// //     return cohort_is_member(PARENTS_COHORT_ID, $userid);
// // }

// // function is_teacher($userid) {
// //     return cohort_is_member(TEACHERS_COHORT_ID, $userid);
// // }

// // function is_student($userid) {
// //     return cohort_is_member(STUDENTS_COHORT_ID, $userid);
// //}

// // for convenience in debuggging
// function is_admin($userid) {
//     return true;
// }

// function is_parent($userid) {
//     return true;
// }

// function is_teacher($userid) {
//     return true;
// }

// function is_student($userid) {
//     return false;
// }

// function get_user_activity_enrollments($userid) {
//     global $DB;
//     return $DB->get_recordset_sql("
// select
//     crs.id as course_id,
//     enrl.id as enrolid,
//     usr.idnumber as idnumber,
//     concat(usr.firstname, ' ', usr.lastname) as username,
//     regexp_replace(crs.fullname, '\(.*\)', '') as fullname
// from ssismdl_enrol enrl
//     join ssismdl_user_enrolments usrenrl
//         on usrenrl.enrolid = enrl.id
//     join ssismdl_course crs
//         on enrl.courseid = crs.id
//     join ssismdl_course_categories cat
//         on crs.category = cat.id
//     join ssismdl_user usr
//         on usrenrl.userid = usr.id
// where
//     crs.visible = 1 and
//     usr.id = ".$userid." and
//     cat.path like '/1/%' and
//     enrl.enrol = 'self'");
// }

// function get_family_activity_enrollments($familyid) {
//     global $DB;
//     return $DB->get_recordset_sql("
// select
//     crs.id as course_id,
//     usr.idnumber as idnumber,
//     concat(usr.firstname, ' ', usr.lastname) as username,
//     regexp_replace(crs.fullname, '\(.*\)', '') as fullname
// from ssismdl_enrol enrl
//     join ssismdl_user_enrolments usrenrl
//         on usrenrl.enrolid = enrl.id
//     join ssismdl_course crs
//         on enrl.courseid = crs.id
//     join ssismdl_course_categories cat
//         on crs.category = cat.id
//     join ssismdl_user usr
//         on usrenrl.userid = usr.id
// where
//     crs.visible = 1 and
//     usr.idnumber like '".$familyid."%' and
//     cat.path like '/1/%' and
//     enrl.enrol = 'self'");
// }

// function derive_plugin_path_from($stem) {
//     // Moodle really really should be providing a standard way to do this
//     return "/local/ssisactivitycenter/{$stem}";
// }


