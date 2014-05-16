<?php

require_once dirname(dirname(dirname(__DIR__))) . '/config.php';

require_login();

// Include the goodies for this block
require dirname(__DIR__) . '/HomeworkBlock/Block.php';
$hwblock = new \SSIS\HomeworkBlock\Block;

$action = required_param('action', PARAM_RAW);
$homeworkID = required_param('homeworkid', PARAM_RAW);

$hw = \SSIS\HomeworkBlock\HomeworkItem::load($homeworkID);

// Check permissions
if (!$hwblock->canEditHomeworkItem($hw)) {
	die("You don't have permission to edit that piece of homework.");
}

switch ($action) {

	case 'approve':
		$hw->approved = 1;
		$success = $hw->save();
		break;

	case 'edit':
		$hw->description = required_param('description', PARAM_RAW);
		$success = $hw->save();
		break;

	case 'delete':
		$success = $DB->delete_records('block_homework', array('id' => $hw->id));
		break;
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode(array('success' => $success));
