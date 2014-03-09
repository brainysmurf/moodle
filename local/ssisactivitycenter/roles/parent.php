<?php

require_once '../../../config.php';
require_once '../lib.php';
require_once '../output.php';

require_login();

setup_page();
output_tabs('Parent');

if (!is_parent($USER->id)) {
    echo 'Only parent accounts can access this section. Contact the DragonNet administrator if you think you should have access.';
    die();
}

# sql query
global $DB;
global $USER;
$family_id = str_replace('P', '', $USER->idnumber);
$family_id = '3158';

$info_string = 'This is a list of enrollments of your children into SSIS Activities.';
$sql = "
select
    crs.id as course_id,
    cat.path as path,
    bus.bus as bus,
    concat(usr.firstname, ' ', usr.lastname) as child,
    regexp_replace(crs.fullname, '\(.*\)', '') as fullname

from ssismdl_enrol enrl
    join ssismdl_user_enrolments usrenrl
        on usrenrl.enrolid = enrl.id
    join ssismdl_course crs
        on enrl.courseid = crs.id
    join ssismdl_user usr
        on usrenrl.userid = usr.id
    join ssismdl_course_categories cat
        on crs.category = cat.id
    join ssismdl_user_activity_bus bus
        on usr.id = bus.userid
where
    crs.visible = ? and
    usr.idnumber like ? and
    usr.idnumber not like ? and
    enrl.enrol = ?
";
$params = array('1', $family_id.'%', '%P', 'self');


if (!empty($family_id)) {
    $results = $DB->get_recordset_sql($sql, $params);
    if (!empty($results)) {
        echo '<div class="local-alert"><i class="icon-info-sign pull-left icon-2x"></i>'.$info_string.'</div>';
        echo "<br />";

        echo '<table class="userinfotable htmltable" width="100%">';
        echo "<thead>";

        foreach ($results as $item) {
            echo '<tr class="r0">';
            echo '<td class="cell c0"><p>'.$item->child.'</p></td>';
            echo '<td class="cell c1 lastcol"><p>'.$item->fullname.'</p></td>';
            if ($item->path === '/1/118/122/123' or
                $item->path === '/1/118/122/124' or
                $item->path === '/1/118/122/125') {
                $item->bus_string = ($item->bus ===1 ? "YES" : "NO");
            } else {
                $item_bus_string = "N/A";
            }
            echo '<td class="cell c1 lastcol"><p>'.$item->bus_string.'</p></td>';

            echo '</tr>';
        }
        $results->close();

    } else {

        echo '<div class="local-alert"><i class="icon-info-sign pull-left icon-2x"></i>This page will display a list of activites your children are enrolled in.</div>';

    }

    echo "<tbody>";

} else {
    echo '<div class="local-alert"><i class="icon-thumbs-down pull-left icon-4x"></i><strong>Apologies, there is something wrong with your account.</strong> <br />Please contact Adam Morris at adammorris@ssis-suzhou.net with the subject header "Message from DragonNet".<br />Be sure to include your DragonNet username. Either that, or your account isn\'t a parent account."</a>"';
}


echo '</tbody></table>';

echo $OUTPUT->footer();

