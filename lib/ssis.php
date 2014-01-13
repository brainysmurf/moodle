<?php

class SSIS
{

	/*
		Sets the users "password2" which is used by pam_pgsql to authenticate
		with moodle users on the mail server
	*/
	public static function update_user_password2($userid, $clearpassword)
	{
		global $DB, $CFG;
		
		$hashedpassword = md5($clearpassword . $CFG->passwordsaltmain);
		
		$DB->set_field('user', 'password2', $hashedpassword, array('id'=>$userid));	
	}
	
}

?>