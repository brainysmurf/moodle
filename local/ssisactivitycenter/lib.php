<?php
require_once '../../config.php';
require_once '../../cohort/lib.php';

// definitions
define('ACTIVITIES_COHORT', 'activitiesHEAD');
$activities_cohort = $DB->get_record('cohort', array('idnumber'=>ACTIVITIES_COHORT), 'id', MUST_EXIST);
define('ACTIVITIES_COHORT_ID', $activities_cohort->id);

function permit_user($userid) {
    if (has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM))) {
        return true;
    }
    return cohort_is_member(ACTIVITIES_COHORT_ID, $userid);
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
    return "/local/ssisactivitycenter/{$stem}";
}

// function declarations
function output_forms($user=null) {  #"Look up by lastname, firstname, or homeroom...") {
    if (!($user)) {
        $default_words = 'placeholder="Look up by lastname, firstname, or homeroom..." ';
        $powerschoolID = "";
    } else {
        // make sure the the text box displays the right thing, depending on context
        $default_words = 'value="'.$user->lastname.', '.$user->firstname.' ('.$user->department.')" ';
        $powerschoolID = $user->idnumber;
    }
    $path_to_index = derive_plugin_path_from("index.php");
    $path_to_query = derive_plugin_path_from("query.php");

    echo '
<form name="user_entry" action="'.$path_to_index.'" method="get">
<input name="" autofocus="autofocus" size="100" onclick="this.select()"
    style="font-size:18px;margin-bottom:5px;box-sizing: border-box;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;padding:3px;"
    type="text" id="person" '.$default_words.'/><br />
<input name="powerschool" type="hidden" id="powerschool" value="'.$powerschoolID.'"/>
<input name="action" style="border:4;" type="submit" type="submit" value="view family"/>
<input name="action" type="submit" value="view child"/>
<input name="action" type="submit" value="enrol child"/>
<input name="action" type="submit" value="deenrol child"/>
</form><br />';
    echo '
<script>
$("#person").autocomplete({
            source: "'.$path_to_query.'",
            select: function (event, ui) {
                console.log("select");
                event.preventDefault();
                $("#person").val(ui.item.label);
                $("#powerschool").val(ui.item.value);
            },
            change: function (event, ui) {   // TODO: determine if I really really need this
                if (ui != null) {
                    event.preventDefault();
                console.log("change");
                }
            },
            focus: function (event, ui) {
                event.preventDefault();
                $("#person").val(ui.item.label);
            },
        });
</script>';
}
