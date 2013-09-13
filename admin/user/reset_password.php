<?php
/**
* Change a user's password to 'changeme'
*/

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$userID = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

require_login();
admin_externalpage_setup('resetpassword'); //idk what this is
require_capability('moodle/user:update', context_system::instance());


//Get the user
$user = $DB->get_record('user', array('id'=>$userID), '*', MUST_EXIST);

#$return = $CFG->wwwroot.'/'.$CFG->admin.'/user/user_bulk.php';

echo $OUTPUT->header();



	if ( $confirm and confirm_sesskey() )
	{		
		$authplugin = get_auth_plugin($user->auth);
		
		$newPassword = 'changeme';
		
        if ($authplugin->can_change_password())
        {
            if ( $result = $authplugin->user_update_password($user, $newPassword) )
            {
            	//Done!
            	echo $OUTPUT->heading('Password Changed Successfully');
            	
            	$subject = 'Your new DragonNet password';
$body = "Dear user,

This is an automated message. No reply necessary.

The password for your SSIS DragonNet account has been reset. Your login details are as follows:

Your username is: {$user->username}
Your password: $newPassword

You can login at http://dragonnet.ssis-suzhou.net/login

Thank you and kind regards,
SSIS DragonNet Admin Team";
            	
            	echo '<div class="singlebutton"><a href="mailto:'.$user->email.'?subject='.rawurlencode($subject).'&body='.rawurlencode($body).'"><button>Send Email to User</button></a></div>';
            	echo '<div class="singlebutton"><a href="#" onclick="history.go(-2);"><button>Continue</button></div>';
            }
            else
            {
                print_error('cannotupdatepasswordonextauth', '', '', $user->auth);
            }
		}
			
	}
	else
	{
		//Show confirmation page
		echo $OUTPUT->heading(get_string('confirmation', 'admin'));
	    $formcontinue = new single_button(new moodle_url('reset_password.php', array('confirm' => 1, 'id'=>$userID)), get_string('yes'));
	    $formcancel = new single_button(new moodle_url($_SERVER['HTTP_REFERER']), get_string('no'), 'get');
	    echo $OUTPUT->confirm('Are you sure you want to reset <strong>'.$user->username.'</strong>\'s password?', $formcontinue, $formcancel);
	}

echo $OUTPUT->footer();
