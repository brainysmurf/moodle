<?php

/**
 * Displays all the homework for a specific course
 */

require 'include/header.php';

// Are we viewing the form or adding stuff?
$action = optional_param('action', 'view', PARAM_RAW);
$courseid = optional_param('courseid', '', PARAM_INT);

if ($courseid) {
	$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
	$context = \context_course::instance($courseid);
	require_capability('block/homework:addhomework', $context);
}

echo $OUTPUT->header();

echo $hwblock->display->tabs('add');
$mode = $hwblock->mode();

switch ($action) {

	/**
	 * Display an existing item in the form to make changes
	 */
	case 'edit':

		if ($mode != 'teacher') {
			die("You need to be in teacher or teacher mode to edit homework.");
		}

		define('FORMACTION', 'edit');
		$editid = required_param('editid', PARAM_INT);
		// Load the existing item
		$editItem = \SSIS\HomeworkBlock\HomeworkItem::load($editid);
		if (!$editItem) {
			die("Unable to find that homework item.");
		}
		break;

	/**
	 * Save changes made from edit action
	 */
	case 'saveedit':
		if ($mode != 'teacher') {
			die("You need to be in teacher or teacher mode to edit homework.");
		}

		// Check permissions
		$context = \context_course::instance($courseid);
		require_capability('block/homework:addhomework', $context);

		$editid = required_param('editid', PARAM_INT);
		$courseid = required_param('courseid', PARAM_INT);
		$groupid = optional_param('groupid', null, PARAM_INT);
		$description = required_param('description', PARAM_RAW);
		$startdate = required_param('startdate', PARAM_RAW);
		$assigneddates = required_param('assigneddates', PARAM_RAW);
		$duedate = optional_param('duedate', null, PARAM_RAW);
		$duration = required_param('duration', PARAM_RAW);

		// Load the existing item
		$homeworkItem = $DB->get_record('block_homework', array('id' => $editid), '*', MUST_EXIST);
		$homeworkItem->courseid = $courseid;
		$homeworkItem->groupid = $groupid;
		$homeworkItem->description = $description;
		$homeworkItem->startdate = $startdate;
		$homeworkItem->duedate = $duedate;
		$homeworkItem->duration = $duration;

		if ($DB->update_record('block_homework', $homeworkItem)) {

			$homeworkItem = \SSIS\HomeworkBlock\HomeworkItem::load($editid);

			// Remove all existing assigned dates
			$homeworkItem->clearAssignedDates();

			// Now add the assigned dates
			$assigneddates = explode(',', $assigneddates);
			foreach ($assigneddates as $date) {
				$homeworkItem->addAssignedDate($date);
			}

			echo '<div class="alert alert-success"><i class="icon-ok"></i> Changes saved.</div>';

		} else {
			echo '<div class="alert alert-error"><i class="icon-delete"></i> There was an error saving the changes.</div>';
		}

		break;


	case 'save':

		if ($mode != 'student' && $mode != 'teacher') {
			die("You need to be in student or teacher mode to add homework.");
		}

		$courseid = required_param('courseid', PARAM_INT);
		$groupid = optional_param('groupid', null, PARAM_INT);
		$description = required_param('description', PARAM_RAW);
		$startdate = required_param('startdate', PARAM_RAW);
		$assigneddates = required_param('assigneddates', PARAM_RAW);
		$duedate = optional_param('duedate', null, PARAM_RAW);
		$duration = required_param('duration', PARAM_RAW);

		// If adding a new item
		$homeworkItem = new stdClass();
		$homeworkItem->approved = $hwblock->canApproveHomework($courseid) ? 1 : 0;
		$homeworkItem->userid = $USER->id;
		$homeworkItem->added = time();

		$homeworkItem->courseid = $courseid;
		$homeworkItem->groupid = $groupid;
		$homeworkItem->description = $description;
		$homeworkItem->startdate = $startdate;
		$homeworkItem->duedate = $duedate;
		$homeworkItem->duration = $duration;

		if ($id = $DB->insert_record('block_homework', $homeworkItem)) {

			$homeworkItem = \SSIS\HomeworkBlock\HomeworkItem::load($id);

			// Now add the assigned dates
			$assigneddates = explode(',', $assigneddates);
			foreach ($assigneddates as $date) {
				$homeworkItem->addAssignedDate($date);
			}

			if ($homeworkItem->approved) {

				$visible = $homeworkItem->startdate >= $hwblock->today;

				if ($visible) {
					echo '<div class="alert alert-success"><i class="icon-ok"></i> The homework has been submitted successfully and is now visible to students in the class.</div>';
				} else {
					echo '<div class="alert alert-success"><i class="icon-pause"></i> The homework has been submitted successfully and will become visible to students on ' . date('l M jS', $homeworkItem->assigned) . '</div>';
				}

			} else {
				echo '<div class="alert alert-success"><i class="icon-ok"></i> The homework has been submitted successfully and will become visible to everybody in the class once a teacher approves it.</div>';
			}

			echo '<hr/>';
		} else {
			echo '<div class="alert alert-error"><i class="icon-delete"></i> There was an error adding the homework.</div>';
		}

		break;

	case 'add':
	default:
		define('FORMACTION', 'add');
		break;
}

switch ($hwblock->mode()) {

	case 'student': $explanation = 'You may add homework for everyone in your class, but the teacher will have to approve it before it appears on DragonNet.';
	break;

	case 'teacher': $explanation = 'You may directly add homework for every student in a class. It does not need to approved separately.';
	break;
}

if (defined('FORMACTION')) {
	echo $hwblock->display->sign('plus-sign', 'Add Homework', $explanation);
	// echo '<h2><i class="icon-plus"></i> Add Homework</h2>';
	include 'include/add_form.php';
}

echo $OUTPUT->footer();
