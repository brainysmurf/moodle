<?php

require_once dirname(dirname(dirname(__DIR__))) . '/config.php';

require_login();

// Include the goodies for this block
require dirname(__DIR__) . '/HomeworkBlock/Block.php';
$hwblock = new \SSIS\HomeworkBlock\Block;

$q = required_param('q', PARAM_RAW);

$sql = 'SELECT id, idnumber, firstname, lastname
FROM {user}
WHERE
	(id = ?
	OR idnumber = ?
	OR LOWER(CONCAT(firstname, \' \', lastname)) LIKE ?)
	AND deleted = 0
ORDER BY firstname, lastname ASC';

$words = explode(' ', $q);

$values = array(
	intval($q),
	intval($q),
	strtolower('%' . implode('%', $words) . '%')
);
$records = $DB->get_records_sql($sql, $values);

header('Content-type: application/json');
echo json_encode(array('users' => $records));
