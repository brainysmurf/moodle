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

		echo $hwblock->display->sign('calendar', "Grade {$grade} Overview", "This page shows homework assigned this week for grade {$grade} classes.");

		// Load classes in this grade

		$groups = $hwblock->getAllGroups($grade);
		$groupIDs = $hwblock->extractGroupIDsFromTimetable($groups);

		$stats = new \SSIS\HomeworkBlock\HomeworkStats($hwblock);
		$stats->setGroupIDs($groupIDs);

		echo $hwblock->display->weekStats($stats);

		// Show classes in this grade

		echo '<hr/>';

		echo '<h2><i class="icon-group"></i> Grade ' . $grade . ' Classes</h2>';
		$classes = $hwblock->getAllGroups($grade);
		echo $hwblock->display->classList($classes);

		break;
}

echo $OUTPUT->footer();
