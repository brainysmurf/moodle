<?php

/**
 * Display all the grades (years) in the school
 */

require 'include/header.php';
echo $OUTPUT->header();

echo $hwblock->display->tabs('grades');

switch ($hwblock->mode()) {

	case 'pastoral':

		echo '<h2><i class="icon-group"></i> All Grades</h2>';

		echo '<ul class="buttons">';
		for ($grade = 1; $grade <= 12; $grade++) {

			echo '<li>';
				echo '<a class="btn" href="grade.php?grade=' . $grade . '">Grade ' . $grade . '</a>';
			echo '</li>';

		}
		echo '</ul>';

		break;
}

echo $OUTPUT->footer();
