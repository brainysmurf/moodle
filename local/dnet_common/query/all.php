<?php

$term = isset($_GET['term']) ? $_GET['term'] : FALSE;
require_once '../../../config.php';
header('Content-type: application/json');
require_login();

if ($term) {

    // Query is being performed
    $term = str_replace(' ', '', strtolower($term));

    $results = array();
    $params = array();

    // query that gets any match of firstname, lastname, or homeroom
    // and ensures that everything returned is a student
    $where = "
(
    deleted = 0 AND
    idnumber != '' AND
        (
            REPLACE(CONCAT(LOWER(firstname), LOWER(lastname)),  ' ', '') LIKE ? OR
            REPLACE(CONCAT(LOWER(lastname),  LOWER(firstname)), ' ', '') LIKE ? OR
            REPLACE(LOWER(firstname), ' ', '') LIKE ? OR
            LOWER(lastname) LIKE ?
        )
)
";
    $params[] = $term.'%';
    $params[] = $term.'%';
    $params[] = $term.'%';
    $params[] = $term.'%';

    $sort = 'lastname, firstname, email, department';
    $fields = 'id, idnumber, lastname, firstname, email, department';

    // execute the query, and step through them
    $students = $DB->get_records_select("user", $where, $params, $sort, $fields);
    foreach ($students as $row) {
        $kind = "Other";
        if (strpos($row->email, '@student.ssis-suzhou.net') !== false) {
            $kind = "Student, ".$row->department;
        // We check for parent first, because some parents will use their school email address
        } else if (strpos($row->idnumber, "P") === 4) {
            $kind = "Parent";
        } else if (strpos($row->email, "@ssis-suzhou.net") !== false) {
            $kind = "Staff";
        }
        $results[] = array(
            "label"=>"{$row->firstname} {$row->lastname} ({$kind})",
            "value"=>$row->idnumber
            );

    }

    echo json_encode($results);
}
