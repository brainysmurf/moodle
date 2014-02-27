<?php

// requirements
require_once '../../config.php';
require_once 'lib.php';

require_login();

if (!permit_user($USER->id)) {
    die('Only members of the cohort '.ACTIVITIES_COHORT.' can access this section. Contact the DragonNet administrator if you think you should have access.');
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url(derive_plugin_path_from('index.php'));
$PAGE->set_title("SSIS Activity Center");
$PAGE->set_heading("SSIS Activity Center");

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
            echo '<td class="cell c0"><p><a href="'.derive_plugin_path_from('index.php').'?action=enrol+child&powerschool='.$item->idnumber.'">'.$item->username.'</a></p></td>';
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
                echo '<td class="cell c1"><p><a href="'.derive_plugin_path_from('index.php').'?action=enrol+child&go='.$row->id.'&powerschool='.$user->idnumber.'">'.$row->fullname.'</a></p></td>';
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
                echo '<td class="cell c1"><p><a href="'.derive_plugin_path_from('index.php').'?action=deenrol+child&go='.$item->enrolid.'&powerschool='.$user->idnumber.'">'.$item->fullname.'</a></p></td>';
                echo '</tr>';
            }
            $results->close();

            echo $end_table;
        }
    }
}

echo $OUTPUT->footer();
