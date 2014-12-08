<?php

require 'include/header.php';

$mode = required_param('mode', PARAM_RAW);
if ($hwblock->changeMode($mode)) {
	redirect('/blocks/homework');
} else {
	die('Invalid mode.');
}
