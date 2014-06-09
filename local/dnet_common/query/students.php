<?php

$q = isset($_GET['term']) ? $_GET['term'] : FALSE;
require_once '../../../config.php';
header('Content-type: application/json');

//require_login();

if ($q) {

    // Query is being performed
    #$term = str_replace(' ', '', strtolower($term));
    #$results = array();
    #$params = array();

    // query that gets any match of firstname, lastname, or homeroom
    // and ensures that everything returned is a student
    $where = "
(
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
)
";

$words = explode(' ', $q);
$wildq = strtolower('%' . implode('%', $words) . '%');

$params = array(
	intval($q), // userID
	intval($q), // idnumber
	strtolower($q), // department
	$wildq,
	$wildq,
	$wildq,
	$wildq,
);

    $sort = 'firstname, lastname, department';
    $fields = 'id, idnumber, lastname, firstname, department';

    $results = array();

    // execute the query, and step through them
    $students = $DB->get_records_select("user", $where, $params, $sort, $fields);
    foreach ($students as $row) {
        $results[] = array(
            "label"=>"{$row->firstname} {$row->lastname} ({$row->department})",
            "value"=>$row->idnumber
            );

    }

    echo json_encode($results);
}
