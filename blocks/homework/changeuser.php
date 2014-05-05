<?php

require '../../config.php';
$userid = required_param('userid', PARAM_INT);

//TODO: Permissions check here?

if ($SESSION->homeworkBlockMode == 'pastoral') {
	$SESSION->homeworkBlockMode = 'pastoral-student';
}

$SESSION->homeworkBlockUser = $userid;
header('Location: index.php');
