<?php
/**
* Insert a user into the table which gets read by a cron job to reset the user's email password
*/

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$userID = optional_param('id', false, PARAM_INT);
if (!$userID) {
	redirect('/admin/user.php');
	exit();
}
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$ref = optional_param('ref', '/admin/user.php' , PARAM_TEXT);

$tableName = 'user_email_password_reset';

require_login();
require_capability('moodle/user:update', context_system::instance());
$PAGE->set_title("Reset A User's Email Password");
$PAGE->set_heading("Reset A User's Email Password");

//Get the user
$user = $DB->get_record('user', array('id'=>$userID), '*', MUST_EXIST);

echo $OUTPUT->header();

	if ( $confirm and confirm_sesskey() )
	{		
		#$newPassword = 'changeme';
		
		$manager = $DB->get_manager();
		
		if ( !$manager->table_exists($tableName) )
		{
			$fullTableName = $CFG->prefix.$tableName;
			$DB->execute("
CREATE TABLE $fullTableName
(
  userid character varying,
  powerschoolid character varying NOT NULL,
  name character varying,
  email character varying,
  CONSTRAINT {$fullTableName}_powerschoolid PRIMARY KEY (powerschoolid)
)
WITH (
  OIDS=FALSE
)");
			echo '<p>Table created. Purge all caches and try again.</p>';
			echo $OUTPUT->footer();
			exit();
		}	
		
		$row = new stdClass();
		$row->userid = $user->id;
		$row->powerschoolid = $user->idnumber;
		$row->name = $user->firstname.' '.$user->lastname;
		$row->email = $user->email;
		
        if ( $ID = $DB->insert_record('user_email_password_reset', $row , false) )
        {
			echo $OUTPUT->heading('Changes Saved');
			echo '<p style="text-align:center;">It will take up to 5 minutes for changes to take effect.</p>';
           	echo '<div class="singlebutton"><a href="'.$ref.'"><button>Continue</button></div>';
		}
		else
		{
			echo '<p style="text-align:center;">Unable to insert to database.</p>';
		}

	}
	else
	{
		//Show confirmation page
		echo $OUTPUT->heading(get_string('confirmation', 'admin'));
	    $formcontinue = new single_button(new moodle_url('reset_email_password.php', array('confirm' => 1, 'id'=>$userID, 'ref'=>$ref)), get_string('yes'));
	    $formcancel = new single_button(new moodle_url($ref), get_string('no'), 'get');
	    echo $OUTPUT->confirm('Are you sure you want to reset <strong>'.$user->username.'</strong>\'s email password?', $formcontinue, $formcancel);
	}

echo $OUTPUT->footer();
