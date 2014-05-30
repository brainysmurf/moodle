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

		// Get all courses in this grade
		$sql = 'SELECT crs.id, crs.fullname
		FROM {course} crs
		JOIN {course_ssis_metadata} crsmd ON crsmd.courseid = crs.id
		WHERE
			(crsmd.field = \'grade\' AND crsmd.value = ?)
			OR
			(crsmd.field = \'grade2\' AND crsmd.value = ?)
		';

		$courses = $DB->get_records_sql($sql, array($grade, $grade));
		$courseIDs = $hwblock->coursesToIDs($courses);

		$stats = new \SSIS\HomeworkBlock\HomeworkStats($hwblock);
		$stats->setCourseIDs($courseIDs);

		echo $hwblock->display->weekStats($stats);

		// Show classes in this grade

		echo '<hr/>';

		echo '<h2><i class="icon-group"></i> Grade ' . $grade . ' Classes</h2>';
		$classes = $hwblock->getAllGroupsFromTimeable($grade);
		echo $hwblock->display->classList($classes);

		break;
}

echo $OUTPUT->footer();
