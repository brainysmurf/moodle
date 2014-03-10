<?php

require_once '../../../config.php';
require_once '../lib.php';
require_once '../output.php';

require_login();
setup_account_management_page();

$powerschoolID = optional_param('powerschool', '', PARAM_RAW);
if (!empty($powerschoolID)) {
    $user = $DB->get_record('user', array('idnumber'=>$powerschoolID));
    $family_id = substr($powerschoolID, 0, 4);
}
$reset_password = optional_param('reset_password', '', PARAM_RAW);

output_tabs('Teacher');

if (!is_teacher($USER->id)) {
    echo 'Only teacher accounts can access this section. Contact the DragonNet administrator if you think you should have access.';
    die();
}

if ( empty($powerschoolID) )  {
    output_forms(null, 'Start typing student\'s first or last name');
} else {

    if ($reset_password == "YES") {

        $newPassword = 'changeme';
        $authplugin = get_auth_plugin($user->auth);

        //if ( $result = $authplugin->user_update_password($user, $newPassword) ) {
        if (true) {
            echo $OUTPUT->heading('Password for '.$user->firstname. ' '.$user->lastname.' Changed Successfully to "changeme"');
        }
        } else {
            echo $user->firstname. ' '. $user->lastname. ' could not be changed, probably because they do not have an activated account. Contact the DragonNet administrator.';
        }
        echo '<ul class="buttons"><li><a class="btn" href="'.derive_plugin_path_from('roles/teacher').'">Return</a></li></ul>';

    } else {

        output_forms($user, 'Site Admin');

        $table = new html_table();
        $table->attributes['class'] = 'userinfobox';

        $row = new html_table_row();

        $row->cells[0] = new html_table_cell();
        $row->cells[0]->attributes['class'] = 'left side';
        $row->cells[0]->text = $OUTPUT->user_picture($user, array('size' => 100, 'courseid'=>1));

        $row->cells[1] = new html_table_cell();
        $row->cells[1]->attributes['class'] = 'content';
        $row->cells[1]->text = $OUTPUT->container(fullname($user, true), 'username');
        $row->cells[1]->text .= '<table class="userinfotable">';

        foreach (array('idnumber', 'email') as $field) {
            $row->cells[1]->text .= '<tr>
                <td>'.get_user_field_name($field).'</td>
                <td>'.s($user->{$field}).'</td>
            </tr>';
        }

        $row->cells[1]->text .= '</table>';

        $table->data = array($row);
        echo html_writer::table($table);
        echo '<ul class="buttons">';
        echo '<form id="reset_password" action="" method="get">';
        echo '<input name="powerschool" type="hidden" value="'.$user->idnumber.'"/>';
        echo '<input name="reset_password" type="hidden" id="reset_passwrod" value="YES"/>';
        echo '<a href="#" class="btn" id="reset_button"><i class="icon-key"></i> Reset this student\'s password</a>';
        echo '</form>';
        echo '</ul>';
        echo '
<script>
$("#reset_button").on("click", function(e) {
    e.preventDefault();
    $("#reset_password").submit();
});
</script>';
    }
}

echo $OUTPUT->footer();


