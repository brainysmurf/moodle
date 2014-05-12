<?php

/**
 * Display all the grades (years) in the school
 */

require 'include/header.php';

$grade = required_param('grade', PARAM_INT);

echo $OUTPUT->header();

echo $hwblock->display->tabs('grades');

switch ($hwblock->mode()) {

	case 'pastoral':

		echo '<h2><i class="icon-group"></i> Grade ' . $grade . ' Overview</h2>';

		?><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
		tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
		quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
		consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
		cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
		proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p><?php

		break;
}

echo $OUTPUT->footer();
