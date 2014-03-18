<?php

require_once '../../../config.php';
require_once '../../../local/dnet_common/sharedlib.php';
require_once '../portables.php';
require_once '../output.php';

// setup_page();
global $PAGE;
global $OUTPUT;

setup_page();

require_login();

output_tabs('For: Secretaries');

if (!is_secretary()) {
    death('Only designated Administrators can access this section.');
}

$powerschoolID = optional_param('powerschool', '', PARAM_RAW);
if (!empty($powerschoolID)) {
    $user = $DB->get_record('user', array('idnumber'=>$powerschoolID));
    if (!$user) {
        death('Something is wrong with the accounts associated with powerschool ID '.$powerschoolID.' you need to contact the DragonNet administrator.');
    }
    $family_id = substr($powerschoolID, 0, 4);
}
$reset_password = optional_param('reset_password', '', PARAM_RAW);

if ( empty($powerschoolID) )  {
    ?>

    <div class="local-alert"><i class="icon-info-sign icon-4x pull-left"></i> <p style="font-weight:bold;font-size:18px;">About Resetting DragonNet accounts</p> You can reset anyone's DragonNet account. You can reset parent accounts by looking up their children first. After resetting, they will need to login to DragonNet with their login and the password "changeme".</div>

    <?php

    output_forms(null, 'Start typing anyone\'s name', 'all');
} else {
    if ($reset_password == "YES") {
        $newPassword = 'changeme';
        $authplugin = get_auth_plugin($user->auth);

        if ( $result = $authplugin->user_update_password($user, $newPassword) ) {
            echo $OUTPUT->heading('Password for '.$user->firstname. ' '.$user->lastname.' Changed Successfully to "changeme"');
        } else {
            echo $user->firstname. ' '. $user->lastname. ' could not be changed, probably because they do not have an activated account. Contact the DragonNet administrator.';
        }
        echo '<ul class="buttons"><li><a class="btn" href="'.derive_plugin_path_from('roles/teacher').'.php">Return</a></li></ul>';
    } else {
        output_forms($user, '', 'all');

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
        echo '<input name="reset_password" type="hidden" id="reset_password" value="YES"/>';
        if (strpos($user->email, '@student.ssis-suzhou.net') !== false) {
            echo '<a href="'.derive_plugin_path_from('roles/secretaries.php?email=YES&powerschool=').$family_id.'P'.'" class="btn" id="parent_instead"><i class="icon-key"></i> Get Parent Account Instead</a>';
        }
        echo '<a href="#" class="btn" id="reset_button"><i class="icon-key"></i> Reset '.$user->firstname.' '.$user->lastname.'\'s password</a>';
        echo '</form>';
        echo '</ul>';
        echo '<div id="dialog" title="Confirm Reset" style="display:none"> Are you sure you want to reset '.$user->firstname.' '.$user->lastname.'\'s password?</div>';
        echo '
<script>
$("#reset_button").on("click", function(e) {
    e.preventDefault();
    $("#dialog").dialog({
        minWidth: 450,
        draggable: false,
        dialogClass: "no-close",
        model: true,
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
