<?php

require_once '../../../config.php';
require_once '../../../local/dnet_common/sharedlib.php';
require_once '../portables.php';
require_once '../output.php';
require_once '../locallib.php';

setup_page();

$powerschoolID = optional_param('powerschool', '', PARAM_RAW);
if (!empty($powerschoolID)) {
    $user = $DB->get_record('user', array('idnumber'=>$powerschoolID));
    if (!$user) {
        death("Sorry, it seems like there is a problem with your account. Please contact help@ssis-suzhou.net with the name of your child(ren).");
    }
    $family_id = substr($powerschoolID, 0, 4);
}
$reset_password = optional_param('reset_password', '', PARAM_RAW);
$email = optional_param('email', '', PARAM_RAW);

output_tabs('For: Parents');

if (isloggedin()) {
    death('This section is intended for parents to look up their DragonNet username and to reset their passwords. You have to be logged out to use it.');
}

if ( empty($powerschoolID) )  {
    output_forms(null, $placeholder="Start typing your child's name, at least two characters is needed.");
} else {

    if ($email == "YES") {
        global $CFG;

        $key = uniqid();

        $row = new stdClass();
        $row->userid = $user->id;
        $row->key = $key;
        $row->time = time();
        $row->used = 0;
        $DB->insert_record('dnet_pwreset_keys', $row);
        $url = $CFG->wwwroot . derive_plugin_path_from("reset_parent_password.php?userID={$user->id}&key={$key}");

        $message_header = get_string('email_msg_parent_body', 'local_dnet_account_management');
        $message_footer = get_string('email_msg_parent_footer', 'local_dnet_account_management');
        $message = $message_header. $url . $message_footer;

        $from = $DB->get_record('user', array('username'=>'lcssisadmin'));

        email_to_user($user, $from, "DragonNet Password Reset Link", $message);

        //mail($user->email, "DragonNet Password Reset Link", $message, "From:DragonNet Admin <lcssisadmin@student.ssis-suzhou.net>");

        echo '<div class="local-alert"><i class="icon-envelope icon-4x pull-left"></i> ';
        echo '<p style="font-weight:bold;font-size:18px;">An email has been sent to "'.mask_email($user->email).'". </p>';
        echo '<p>Please check and click the link to reset your password. The subject is "DragonNet Password Reset Link"; be sure to check your spam inboxes. If you have any further difficulties, please email help@ssis-suzhou.net with your child(ren)\'s name.</p></div>';
        echo '<ul class="buttons">';
        echo '<a href="'.$CFG->wwwroot.'" class="btn"><i class="icon-home "></i> DragonNet Home</a>';
        echo '</ul>';

    } else if ($email == "NO") {

        sign("phone", "Please contact a school secretary", "We need to change your username, and only secretaries can do that manually. Please <a href=\"http://www.ssis-suzhou.net/contact-us/index.aspx\">go to the school website</a> for contact information. You will simply need to tell them the name of your children who attend SSIS.");

//         echo '<div class="local-alert"><i class="icon-question-sign icon-4x pull-left"></i> ';
//         echo '<p>Please copy and paste the below text and send to <strong>help@ssis-suzhou.net</strong></p>';
//         echo '<br />';
//         echo '<textarea onclick="this.select()" style="width:70%;height:200px;padding:10px;">Dear Help,

// I am parent with PowerSchool family id of '.$family_id.'P and I would like to reset the password to my DragonNet account. Right now, the username in DragonNet is incorrect and needs to be changed.

// Please help me to reset it.

// Regards,</textarea>';
//         echo '</div>';


    } else {
        $user = $DB->get_record('user', array('idnumber'=>$family_id.'P'));
        if (!$user) {
            death("Something wrong with your account. Please contact help@ssis-suzhou.net with the name of your child(ren).");
        }
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
        echo '<a href="'.derive_plugin_path_from('roles/parents.php?email=YES&powerschool='.$user->idnumber).'" class="btn" id="reset_button"><i class="icon-thumbs-up"></i> Yes, that is my email address</a>';
        echo '<a href="'.derive_plugin_path_from('roles/parents.php?email=NO&powerschool='.$user->idnumber).'" class="btn" id="reset_button"><i class="icon-thumbs-down"></i> No, that is not my email address</a>';
        echo '<a href="'.derive_plugin_path_from('roles/parents.php').'" class="btn" id="reset_button"><i class="icon-backward "></i> Back</a>';
        echo '</form>';
        echo '</ul>';
    }
}

echo $OUTPUT->footer();


