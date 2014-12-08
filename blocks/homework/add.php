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

		define('FORMACTION', 'edit');
		$editid = required_param('editid', PARAM_INT);
		// Load the existing item
		$editItem = \SSIS\HomeworkBlock\HomeworkItem::load($editid);
		if (!$editItem) {
			die("Unable to find that homework item.");
		}

		if (!$hwblock->canEditHomeworkItem($editItem)) {
			die("You don't have permission to edit that piece of homework.");
		}

		break;

	/**
	 * Save changes made from edit action
	 */
	case 'saveedit':

		$editid = required_param('editid', PARAM_INT);
		$courseid = required_param('courseid', PARAM_INT);
		$private = optional_param('private', null, PARAM_INT);
		$groupid = optional_param('groupid', null, PARAM_INT);
		$title = required_param('title', PARAM_RAW);
		$description = optional_param('description', '', PARAM_RAW);
		$startdate = required_param('startdate', PARAM_RAW);
		$assigneddates = required_param('assigneddates', PARAM_RAW);
		$duedate = optional_param('duedate', null, PARAM_RAW);
		$duration = required_param('duration', PARAM_RAW);

		// Check permissions
		if ($courseid) {
			$context = \context_course::instance($courseid);
			require_capability('block/homework:addhomework', $context);
		}

		// Load the existing item
		$homeworkItem = $DB->get_record('block_homework', array('id' => $editid), '*', MUST_EXIST);

		if (!$hwblock->canEditHomeworkItem($homeworkItem)) {
			die("You don't have permission to edit that piece of homework.");
		}

		$homeworkItem->courseid = $courseid;
		$homeworkItem->groupid = $groupid;
		$homeworkItem->title = $title;
		$homeworkItem->description = $description;
		$homeworkItem->startdate = $startdate;
		$homeworkItem->duedate = $duedate;
		$homeworkItem->duration = $duration;

		// Auto approve when a teacher edits
		if ($mode == 'teacher' && !$homeworkItem->private) {
			$homeworkItem->approved = 1;
		}

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

		$courseid = optional_param('courseid', null, PARAM_INT);
		$groupid = optional_param('groupid', null, PARAM_INT);
		if ($groupid == -1) {
			$groupid = 0;
		}
		$title = required_param('title', PARAM_RAW);
		$description = optional_param('description', '', PARAM_RAW);
		$startdate = required_param('startdate', PARAM_RAW);
		$assigneddates = required_param('assigneddates', PARAM_RAW);
		$duedate = optional_param('duedate', null, PARAM_RAW);
		$duration = required_param('duration', PARAM_RAW);
		$private = optional_param('private', 0, PARAM_INT);

		// If adding a new item
		$homeworkItem = new stdClass();
		$homeworkItem->approved = $hwblock->canApproveHomework($courseid) ? 1 : 0;
		$homeworkItem->userid = $USER->id;
		$homeworkItem->added = time();

		$homeworkItem->courseid = $courseid;
		$homeworkItem->groupid = $groupid;
		$homeworkItem->title = $title;
		$homeworkItem->description = $description;
		$homeworkItem->startdate = $startdate;
		$homeworkItem->duedate = $duedate;
		$homeworkItem->duration = $duration;
		$homeworkItem->private = $private;

		if ($id = $DB->insert_record('block_homework', $homeworkItem)) {

			$homeworkItem = \SSIS\HomeworkBlock\HomeworkItem::load($id);

			// Now add the assigned dates
			$assigneddates = explode(',', $assigneddates);
			foreach ($assigneddates as $date) {
				$homeworkItem->addAssignedDate($date);
			}

			if ($homeworkItem->private) {

				// Student submitted private homework
				echo '<div class="alert alert-success"><i class="icon-ok"></i> The homework has been saved and is visible on <a href="index.php">your overview</a>.</div>';

			} elseif ($homeworkItem->approved && $homeworkItem->startdate <= $hwblock->today) {

				// Approved homework that is visible today or in the past
				echo '<div class="alert alert-success"><i class="icon-ok"></i> The homework has been submitted successfully and is now visible to students in the class.</div>';

			} elseif ($homeworkItem->approved && $homeworkItem->startdate > $hwblock->today) {

				// Approved homework that becomes visible in the future
				echo '<div class="alert alert-success"><i class="icon-pause"></i> The homework has been submitted successfully and will become visible to students on ' . date('l M jS', strtotime($homeworkItem->startdate)) . '</div>';

			} elseif (!$homeworkItem->approved) {

				// Unapproved homework
				echo '<div class="alert alert-success"><i class="icon-ok"></i> The homework has been submitted successfully and will become visible to everybody in the class once a teacher approves it.</div>';

				// Email the teacher
				#$hwblock->emailTeacherOnNewHomework($homeworkItem, $USER);

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

if (defined('FORMACTION')) {

	if (FORMACTION === 'edit') {

		echo $hwblock->display->sign('edit-sign', 'Edit Homework', 'Click the Submit button at the bottom to save your changes.');

	} else if (FORMACTION === 'add' && $mode == 'student') {

		echo $hwblock->display->sign('plus-sign', 'Add Homework', 'You may add homework for either just yourself, or everyone in your class. If the latter, your class teacher will have to approve it before it appears on DragonNet.');

	} else if (FORMACTION === 'add' && $mode == 'teacher') {

		echo $hwblock->display->sign('play-sign', 'Add Homework', 'Here you may add homework for every student in a class to see. It does not need to be approved separately, it will become visible instantly.');

	}

	include 'include/add_form.php';
}

echo $OUTPUT->footer();
