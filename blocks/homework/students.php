<?php

/**
 * Show a list of all students in the school so pastoral staff can switch to one
 */

require 'include/header.php';
echo $OUTPUT->header();

echo $hwblock->display->tabs('students');

switch ($hwblock->mode()) {

	case 'pastoral':

		echo '<h2><i class="icon-group"></i> Students</h2>';
		echo $hwblock->display->studentList();

		break;
}

echo $OUTPUT->footer();
