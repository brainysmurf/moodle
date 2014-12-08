<?php

require_once '../../../config.php';
require_once '../../../local/dnet_common/sharedlib.php';
require_once '../portables.php';
require_once '../output.php';

require_login();
setup_page();

$powerschoolID = optional_param('powerschool', '', PARAM_RAW);
if (!empty($powerschoolID)) {
    $user = $DB->get_record('user', array('idnumber'=>$powerschoolID));
    $family_id = substr($powerschoolID, 0, 4);
}
$reset_password = optional_param('reset_password', '', PARAM_RAW);

output_tabs('For: Teachers');
if (!is_teacher()) {
    death('Only teacher accounts can access this section. Contact the DragonNet administrator if you think you should have access.');
}

if ( empty($powerschoolID) )  {
    ?>

    <div class="local-alert"><i class="icon-info-sign icon-4x pull-left"></i> <p style="font-weight:bold;font-size:18px;">About Resetting DragonNet Accounts</p> You can reset any students' DragonNet, DragonTV, and Student Email password. After resetting, they login to DragonNet with their username and the password "changeme".</div>

    <?php


    output_forms(null, 'Start typing student\'s first or last name');
} else {

    if ($reset_password == "YES") {

        $newPassword = 'changeme';
        $authplugin = get_auth_plugin($user->auth);

        if ( $result = $authplugin->user_update_password($user, $newPassword) ) {
            echo $OUTPUT->heading('DragonNet password for "'.$user->username.'" changed successfully to "changeme"');
            echo '<div class="local-alert"><i class="icon-beer icon-4x pull-left"></i><p> Resetting the DragonNet password also affects <b>Student Email</b> and <b>DragonTV</b>.</p><p>';
            echo 'They will need to change their password <b>on DragonNet first</b> in order for their Student Email and DragonTV passwords to be updated with their new password.</p></i></div>';
        } else {
            echo $user->firstname. ' '. $user->lastname. ' could not be changed, probably because they do not have an activated account. Contact the DragonNet administrator.';
        }
        echo '<ul class="buttons"><li><a class="btn" href="'.derive_plugin_path_from('').'">Return</a></li></ul>';

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
        echo '<div id="dialog" title="Confirm Reset" style="display:none"> Are you sure you want to reset '.$user->firstname.' '.$user->lastname.'\'s password?</div>';
        echo '
<script>
$("#reset_button").on("click", function(e) {
    e.preventDefault();
    $("#dialog").dialog({
        minWidth:450,
        draggable: false,
        dialogClass: "no-close",
        modal: true,
        show: { effect: "drop", duration: 400 },
        buttons: [
            {
                text: "OK",
                click: function() {
                    $("#reset_password").submit();
                }
            },
        ]
    });
});
</script>';
    }
}

echo $OUTPUT->footer();


