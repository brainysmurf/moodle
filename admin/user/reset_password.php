<?php
/**
* Change a user's password to 'changeme'
*/

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$userID = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$ref = optional_param('ref', '/admin/user.php' , PARAM_TEXT);

require_login();
admin_externalpage_setup('resetpassword');
require_capability('moodle/user:update', context_system::instance());

//Get the user
$user = $DB->get_record('user', array('id'=>$userID), '*', MUST_EXIST);

echo $OUTPUT->header();

$newPassword = 'changeme';

	if ( $confirm and confirm_sesskey() )
	{		
		$authplugin = get_auth_plugin($user->auth);
		
        if ($authplugin->can_change_password())
        {
            if ( $result = $authplugin->user_update_password($user, $newPassword) )
            {
            	//Done!
            	echo $OUTPUT->heading('Password Changed Successfully');
            	
            	$subject = 'Your new DragonNet password';
		if ( $user->firstname === "Parent" ) { $salutation = 'Parent'; } else { $salutation = $user->firstname . ' ' . $user->lastname; }
$body = "Dear $salutation,

As requested, the password for your SSIS DragonNet account has been reset. Your login details are as follows:

You can login to DragonNet by going to SSIS homepage, or simply click here: http://dragonnet.ssis-suzhou.net/login

Your username is:
{$user->username}

Your password:
$newPassword

You will be forced to change your password as soon as you log in. Remember, all DragonNet passwords must have a symbol character such as ! # or $

Thank you and kind regards,
SSIS DragonNet Admin Team";
            	
            	echo '<div class="singlebutton"><a href="mailto:'.$user->email.'?subject='.rawurlencode($subject).'&body='.rawurlencode($body).'"><button>Send Email to User</button></a></div>';
            	echo '<div class="singlebutton"><a href="'.$ref.'"><button>Continue</button></div>';
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
	    $formcontinue = new single_button(new moodle_url('reset_password.php', array('confirm' => 1, 'id'=>$userID, 'ref'=>$ref)), get_string('yes'));
	    $formcancel = new single_button(new moodle_url($ref), get_string('no'), 'get');
	    echo $OUTPUT->confirm('Are you sure you want to reset <strong>'.$user->username.'</strong>\'s password to <strong>'.$newPassword.'</strong>?', $formcontinue, $formcancel);
	}

echo $OUTPUT->footer();
