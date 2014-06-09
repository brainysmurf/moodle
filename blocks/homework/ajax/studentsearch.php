<?php

require_once dirname(dirname(dirname(__DIR__))) . '/config.php';

require_login();

// Include the goodies for this block
require dirname(__DIR__) . '/HomeworkBlock/Block.php';
$hwblock = new \SSIS\HomeworkBlock\Block;

$q = required_param('q', PARAM_RAW);

$sql = "SELECT id, idnumber, firstname, lastname
FROM {user}
WHERE
	email LIKE '%@student.ssis-suzhou.net'
	AND (
		id = ?
		OR idnumber = ?
		OR LOWER(department) = ?
		OR REPLACE(CONCAT(LOWER(firstname), LOWER(lastname)),  ' ', '') LIKE ?
		OR REPLACE(CONCAT(LOWER(lastname),  LOWER(firstname)), ' ', '') LIKE ?
		OR LOWER(lastname) LIKE ?
	)
	AND deleted = 0
ORDER BY firstname, lastname ASC";

$words = explode(' ', $q);
$wildq = strtolower('%' . implode('%', $words) . '%');

$values = array(
	intval($q), // userID
	intval($q), // idnumber
	strtolower($q), // department
	$wildq,
	$wildq,
	$wildq,
	$wildq,
);
$records = $DB->get_records_sql($sql, $values);

header('Content-type: application/json');
echo json_encode(array('users' => $records));
