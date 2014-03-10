<?php

require_once '../../../config.php';
require_once '../lib.php';
require_once '../output.php';

// setup_page();
global $PAGE;
global $OUTPUT;

setup_account_management_page();

require_login();

output_tabs('Secretaries');

if (!is_admin($USER->id)) {
    echo 'Only designated Administrators can access this section.';
    die();
}

$powerschoolID = optional_param('powerschool', '', PARAM_RAW);
if (!empty($powerschoolID)) {
    $user = $DB->get_record('user', array('idnumber'=>$powerschoolID));
    $family_id = substr($powerschoolID, 0, 4);
}
$action = optional_param('action', '', PARAM_RAW);
//$go = optional_param('go', '', PARAM_RAW);
//if (empty($go)) { $go=false; }
//$enrol_id = optional_param('enrolid', '', PARAM_INT);

if ( empty($action) or $action === 'input' )  {

    if (empty($user)) {
        output_forms(null, 'Start typing any staff member or student name');
    } else {
        output_forms($user, 'Site Admin');
    }

    if (empty($powerschoolID)) {
        // nothing
     } else {

        output_begin_table('Displaying entire family enrollments; click on student name to make any modifications.');
        foreach ($results as $item) {
            echo '<tr class="r0">';
            echo '<td class="cell c0"><p><a href="'.derive_plugin_path_from('index.php').'?action=enrol+child&powerschool='.$item->idnumber.'">'.$item->username.'</a></p></td>';
            echo '<td class="cell c1 lastcol"><p>'.$item->fullname.'</p></td>';
            echo '</tr>';
        }
        $results->close();

        output_end_table();

     }

} else if ($action==='view child') {


} else if ($action==='deenrol child') {

}

echo $OUTPUT->footer();
