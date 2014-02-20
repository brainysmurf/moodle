<?php

$term = isset($_GET['term']) ? $_GET['term'] : FALSE;
require_once '../config.php';
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
require_once '../cohort/lib.php';
define('ACTIVITIES_COHORT', 'activitiesHEAD');
$activities_cohort = $DB->get_record('cohort', array('idnumber'=>ACTIVITIES_COHORT), 'id', MUST_EXIST);
define('ACTIVITIES_COHORT_ID', $activities_cohort->id);

function output_forms($user=null) {  #"Look up by lastname, firstname, or homeroom...") {
    if (!($user)) {
        $default_words = 'placeholder="Look up by lastname, firstname, or homeroom..." ';
        $powerschoolID = "";
    } else {
        $default_words = 'value="'.$user->lastname.', '.$user->firstname.' ('.$user->department.')" ';
        $powerschoolID = $user->idnumber;
    }
    echo '
<form name="user_entry" action="/activitieshead/index.php" method="get">
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
            source: "/activitieshead/index.php",
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

if ($term) {
    // Taps the database for user information
    global $DB;
    $term = strtolower($term);
    $term_plain = pg_escape_literal($term);
    $term_perc = pg_escape_literal($term.'%');
    $results = array();
    if (strpos($term,',') !== false) {
        $where = "(email LIKE '%@student.ssis-suzhou.net' AND CONCAT(LOWER(lastname), ', ', LOWER(firstname)) like {$term_perc})";
    } else {
        $where = "(email LIKE '%@student.ssis-suzhou.net' AND (LOWER(lastname) LIKE {$term_perc} OR LOWER(firstname) LIKE {$term_perc} OR LOWER(department) = {$term_plain}))";
    }
    $sort = 'lastname, firstname, department';
    $fields = 'id, idnumber, lastname, firstname, department';
    $students = $DB->get_records_select("user", $where, null, $sort, $fields);
    foreach ($students as $row) {
        if ($row->idnumber === "") {
            continue;  // TODO: moodle for some reason can keep useless rows of students....
        }
        $results[] = array(
            "label"=>$row->lastname.', '.$row->firstname.' ('.$row->department.')',
            "value"=>$row->idnumber
            );

    }

    echo json_encode($results);

} else {

    require_login();  // put this back once you got it working

    global $DB;
    $parent_role = $DB->get_record('role', array('shortname'=>'parent'), 'id', MUST_EXIST);
    define('PARENT_ROLE_ID', $parent_role->id);

    global $USER;

    if (!permit_user($USER->id)) {
        die('Only members of the cohort '.ACTIVITIES_COHORT.' can access this section. Contact the DragonNet administrator if you think you should have access.');
    }

    $PAGE->set_context(context_system::instance());
    $PAGE->set_url('/activitieshead/index.php');
    $PAGE->set_title("Activities Head Centre");
    $PAGE->set_heading("Activities Head Centre");

    echo $OUTPUT->header();

    $begin_table = '<table class="userinfotable htmltable" width="100%"><thead></thead><tbody>';
    $end_table = '</tbody></table>';

    $powerschoolID = optional_param('powerschool', '', PARAM_RAW);
    if (!empty($powerschoolID)) {
        $user = $DB->get_record('user', array('idnumber'=>$powerschoolID));
        $family_id = substr($powerschoolID, 0, 4);
    }
    $action = optional_param('action', '', PARAM_RAW);
    $go = optional_param('go', '', PARAM_RAW);
    if (empty($go)) { $go=false; }
    $enrol_id = optional_param('enrolid', '', PARAM_INT);

    if ( empty($action) or $action === 'view family' )  {

        if (empty($user)) {
            output_forms();
        } else {
            output_forms($user);
        }

        if (empty($powerschoolID)) {
            echo "I got nothing";
         } else {
            $results = get_family_activity_enrollments($family_id);

            echo '<div>Displaying entire family enrollments; click on student name to make any modifications.</div><br />';
            echo $begin_table;

            foreach ($results as $item) {
                echo '<tr class="r0">';
                echo '<td class="cell c0"><p><a href="/activitieshead/index.php?action=enrol+child&powerschool='.$item->idnumber.'">'.$item->username.'</a></p></td>';
                echo '<td class="cell c1 lastcol"><p>'.$item->fullname.'</p></td>';
                echo '</tr>';
            }
            $results->close();

            echo $end_table;

         }

    } else if ($action==='view child') {
        if ($go) {
            $enrolid = $go;
            $instance = $DB->get_record('enrol', array('id'=>$enrolid, 'enrol'=>'self'), '*', MUST_EXIST);
            $plugin = enrol_get_plugin('self');
            $plugin->unenrol_user($instance, $user->id);
            echo 'De-enrolled both the child and the parent (although the parent stays enrolled if there is another child enrolled). Please check';
        } else {
            output_forms($user);

            echo '<div>Viewing enrollments for <strong>'.$user->lastname.', '.$user->firstname.'</strong> and his/her linked parent account</div><br />';

            echo $begin_table;
            $results = get_user_activity_enrollments($user->id);

            foreach ($results as $item) {
                echo '<tr class="r0">';
                echo '<td class="cell c0"><p>'.$user->lastname.', '.$user->firstname.'</p></td>';
                echo '<td class="cell c1"><p>'.$item->fullname.'</p></td>';
                echo '</tr>';
            }
            $results->close();

            echo $end_table;
        }

    } else if ($action==='enrol child') {

        if (substr($user->idnumber, '4') === 'P') {
            echo "You can't enroll parents, you just enrol the child and the parent gets enrolled automatically.";
        }

        else if ($go) {
            $courseid = $go;
            $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'self'));
            $plugin = enrol_get_plugin('self');
            $plugin->enrol_user($instance, $user->id);
            echo 'Enrolled the child and the parent account. Please check';

        } else {
            output_forms($user);

            echo '<div>Click to add enrollment for <strong>'.$user->lastname.', '.$user->firstname.'</strong> and his/her linked parent account</div><br />';

            echo $begin_table;

            $my_courses = get_user_activity_enrollments($user->id);
            $already_enrolled = array();
            foreach ($my_courses as $course) {
                $already_enrolled[] = $course->course_id;
            }
            $my_courses->close();

            $results = $DB->get_recordset_sql("
select
    crs.fullname, crs.id
from
    ssismdl_course crs
        join ssismdl_course_categories cat
            on crs.category = cat.id
where
    cat.path like '/1/%'
order by
    crs.fullname");



            foreach ($results as $row) {
                echo '<tr class="r0">';
                if (!in_array($row->id, $already_enrolled)) {
                    echo '<td class="cell c1">Click to enrol:</td>';
                    echo '<td class="cell c1"><p><a href="/activitieshead/index.php?action=enrol+child&go='.$row->id.'&powerschool='.$user->idnumber.'">'.$row->fullname.'</a></p></td>';
                } else {
                    echo '<td class="cell c1">Already enrolled:</td>';
                    echo '<td class="cell c1"><p>'.$row->fullname.'</a></p></td>';
                }
                echo '</tr>';
            }
            $results->close();

            echo $end_table;
        }

    } else if ($action==='deenrol child') {
        if (substr($user->idnumber, '4') === 'P') {
            echo "You can't de-enroll parents, you just enrol the child and the parent gets de-enrolled automatically (unless another child is still enrolled).";
        } else {

            if ($go) {
                $enrolid = $go;
                $instance = $DB->get_record('enrol', array('id'=>$enrolid, 'enrol'=>'self'), '*', MUST_EXIST);
                $plugin = enrol_get_plugin('self');
                $plugin->unenrol_user($instance, $user->id);
                echo 'De-enrolled both the child and the parent (although the parent stays enrolled if there is another child enrolled). Please check';
            } else {
                output_forms($user);

                echo '<div>Removing enrollment for <strong>'.$user->lastname.', '.$user->firstname.'</strong> and his/her linked parent account</div><br />';

                echo $begin_table;
                $results = get_user_activity_enrollments($user->id);

                foreach ($results as $item) {
                    echo '<tr class="r0">';
                    echo '<td class="cell c0"><p>Click to de-enrol:</p></td>';
                    echo '<td class="cell c1"><p><a href="/activitieshead/index.php?action=deenrol+child&go='.$item->enrolid.'&powerschool='.$user->idnumber.'">'.$item->fullname.'</a></p></td>';
                    echo '</tr>';
                }
                $results->close();

                echo $end_table;
            }
        }
    }
}
