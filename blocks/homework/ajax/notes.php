<?php

require_once dirname(dirname(dirname(__DIR__))) . '/config.php';

require_login();

// Include the goodies for this block
require dirname(__DIR__) . '/HomeworkBlock/Block.php';
$hwblock = new \SSIS\HomeworkBlock\Block;

$action = required_param('action', PARAM_RAW);
$homeworkID = required_param('homeworkid', PARAM_RAW);
$notes = required_param('notes', PARAM_RAW);

// Get the item
$hw = \SSIS\HomeworkBlock\HomeworkItem::load($homeworkID);

switch ($action) {

	case 'save':
		$response = array(
			'success' => $hw->setNotes($hwblock->userID(), $notes),
			'text' => $hwblock->display->filterText($notes)
		);
		break;

}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode($response);
