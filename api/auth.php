<?php

/**
 * Check if a user's password is correct, returns minimal information about the user if so.
 * Send a POST request to this page with the user's email ('email') and plaintext password ('password')
 * to use.
 * Returns a JSON response.
 */

require dirname(__DIR__) . '/config.php';
require_once($CFG->dirroot . '/lib/password_compat/lib/password.php');
header('Content-type: application/json');

$response = array();

/**
 * Parameters
 */
if (!empty($_POST['email'])) {
	$email = $_POST['email'];
} else {
	die(json_encode(array('error' => 'Email is required')));
}

if (!empty($_POST['password'])) {
	$password = $_POST['password'];
} else {
	die(json_encode(array('error' => 'Password is required')));
}

/**
 * Load the user's info from the database
 */
$user = $DB->get_record('user', array('email' => $email));
if (!$user) {
	die(json_encode(array('error' => 'User not found')));
}

/**
 * Check the password is correct
 */
if (password_verify($password, $user->password)) {
	// Specify what information to give in the response
	// (Don't want to give unnecessary stuff here)
	$response['user'] = array(
		'id' => $user->id,
		'username' => $user->username,
		'email' => $user->email,
		'auth' => $user->auth
	);
}

echo json_encode($response);
