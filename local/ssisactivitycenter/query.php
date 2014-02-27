<?php

$term = isset($_GET['term']) ? $_GET['term'] : FALSE;

require_once '../../config.php';
require_login();  // put this back once you got it working

if ($term) {

    // Query is being performed
    global $DB;
    $term = strtolower($term);

    $results = array();
    $params = array();

    // query that gets any match of firstname, lastname, or homeroom
    // and ensures that everything returned is a student
    $where = "
(
    deleted = 0 AND
    idnumber != '' AND
    email LIKE '%@student.ssis-suzhou.net' AND
        (
            LOWER(lastname) LIKE ? OR
            REPLACE(LOWER(firstname), ' ', '') LIKE ? OR
            LOWER(department) = ? OR
            CONCAT(LOWER(firstname), ' ', LOWER(lastname)) LIKE ?
        )
)
";
    $params[] = $term.'%';
    $params[] = $term.'%';
    $params[] = $term;
    $params[] = $term.'%';

    $sort = 'lastname, firstname, department';
    $fields = 'id, idnumber, lastname, firstname, department';

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
