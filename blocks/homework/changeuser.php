<?php

require '../../config.php';
$userid = required_param('userid', PARAM_INT);
//TODO: Permissions check here
$SESSION->homeworkBlockUser = $userid;
header('Location: index.php');
