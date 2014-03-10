<?php

require_once '../../../config.php';
require_once '../lib.php';
require_once '../output.php';

setup_account_management_page();

$powerschoolID = optional_param('powerschool', '', PARAM_RAW);
if (!empty($powerschoolID)) {
    $user = $DB->get_record('user', array('idnumber'=>$powerschoolID));
    $family_id = substr($powerschoolID, 0, 4);
}
$reset_password = optional_param('reset_password', '', PARAM_RAW);
$email = optional_param('email', '', PARAM_RAW);

output_tabs('Parent');

if (!is_teacher($USER->id)) {
    echo 'Only teacher accounts can access this section. Contact the DragonNet administrator if you think you should have access.';
    die();
}

if ( empty($powerschoolID) )  {
    output_forms(null, $placeholder="Start typing your child's name");
} else {

    if ($reset_password == "YES") {

        $newPassword = 'changeme';
        $authplugin = get_auth_plugin($user->auth);

        if ( $result = $authplugin->user_update_password($user, $newPassword) ) {
            echo $OUTPUT->heading('Password for '.$user->firstname. ' '.$user->lastname.' Changed Successfully');
        } else {
            echo $user->firstname. ' '. $user->lastname. ' could not be changed, probably because they do not have an activated account. Contact the DragonNet administrator.';
        }
        echo '<ul class="buttons"><li><a class="btn" href="'.derive_plugin_path_from('roles/teacher').'">Return</a></li></ul>';

    } else {

        if ($email == "YES") {
            global $CFG;
            echo '<div class="local-alert"><i class="icon-envelope icon-4x pull-left"></i> ';
            echo '<p>An email has been sent to <strong>'.mask_email($user->email).'</strong>. ';
            echo 'Please check and click the link to reset your password.</p><p>If you have any further difficulties, please email help@ssis-suzhou.net with your child\'s name.</p></div>';
            echo '<ul class="buttons">';
            echo '<a href="'.$CFG->wwwroot.'" class="btn"><i class="icon-home "></i> DragonNet Home</a>';
            echo '</ul>';

        } else if ($email == "NO") {

            echo '<div class="local-alert"><i class="icon-question-sign icon-4x pull-left"></i> ';
            echo '<p>Please copy and paste the below text and send to <strong>help@ssis-suzhou.net</strong></p>';
            echo '<br />';
            echo '<textarea onclick="this.select()" style="width:70%;height:200px;padding:10px;">Dear Help,

I am parent with PowerSchool family id of '.$family_id.'P and I would like to reset the password to my DragonNet account. Right now, the username in DragonNet is incorrect and needs to be changed.

Please help me to reset it.

Regards,</textarea>';
            echo '</div>';


        } else {
            $user = $DB->get_record('user', array('idnumber'=>$family_id.'P'));

            //output_forms($user);

            $table = new html_table();
            $table->attributes['class'] = 'userinfobox';

            $row = new html_table_row();

            $row->cells[0] = new html_table_cell();
            $row->cells[0]->attributes['class'] = 'left side';
            $row->cells[0]->text = $OUTPUT->user_picture($user, array('size' => 100, 'courseid'=>1));

            $row->cells[1] = new html_table_cell();
            $row->cells[1]->attributes['class'] = 'content';
            $row->cells[1]->text = '<div class="username">Is this your email address?</div>';
            $row->cells[1]->text .= '<table class="userinfotable">';

            foreach (array('email') as $field) {
                $row->cells[1]->text .= '<tr>
                    <td>'.get_user_field_name($field).'</td>
                    <td>'.mask_email(s($user->{$field})).'</td>
                </tr>';
            }

            $row->cells[1]->text .= '</table>';

            $table->data = array($row);
            echo html_writer::table($table);
            echo '<ul class="buttons">';
            echo '<form id="reset_password" action="" method="get">';
            echo '<a href="'.derive_plugin_path_from('roles/parent.php?email=YES&powerschool='.$user->idnumber).'" class="btn" id="reset_button"><i class="icon-thumbs-up"></i> Yes, that is my email address</a>';
            echo '<a href="'.derive_plugin_path_from('roles/parent.php?email=NO&powerschool='.$user->idnumber).'" class="btn" id="reset_button"><i class="icon-thumbs-down"></i> No, that is not my email address</a>';
            echo '<a href="'.derive_plugin_path_from('roles/parent.php').'" class="btn" id="reset_button"><i class="icon-backward "></i> Back</a>';
            echo '</form>';
            echo '</ul>';
        }
    }
}

echo $OUTPUT->footer();


