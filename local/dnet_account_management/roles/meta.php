<?php

require_once '../../../config.php';
require_once '../../../local/dnet_common/sharedlib.php';
require_once '../portables.php';
require_once '../output.php';

require_login();
setup_page();

if (!is_admin()) {
    death("This section is for DragonNet administrators only.");
}


$table = new html_table();
$table->attributes['class'] = 'userinfobox';
$table->data = array();
$table->head = array("User", "Date requested");


foreach ($DB->get_records('dnet_pwreset_keys', array()) as $db_row) {

    #print_object($db_row);

    $user = $DB->get_record('user', array("id"=>$db_row->userid));

    $row = new html_table_row();
    $row->attributes['class'] = 'test';

    $row->cells[0] = new html_table_cell();
    $row->cells[0]->text .= $user->idnumber. ': '. $user->firstname . ' ' . $user->lastname;

    $row->cells[1] = new html_table_cell();
    $row->cells[1]->text .= date('F d, Y', $timestamp=$db_row->time);

    $table->data[] = $row;

}

echo html_writer::table($table);

echo $OUTPUT->footer();
