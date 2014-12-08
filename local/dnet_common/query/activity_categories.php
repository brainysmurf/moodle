<?php

$term = isset($_GET['term']) ? $_GET['term'] : FALSE;
require_once '../../../config.php';
header('Content-type: application/json');

//require_login();
$results = array();


if ($term) {
    // Query is being performed
    $term = str_replace(' ', '', strtolower($term));
    $params = array();

    $sql = "
SELECT
    cat.name, cat.id
FROM
    {course_categories} cat
WHERE
    REPLACE(LOWER(cat.name), ' ', '') LIKE ?
";

    // query that gets any match of an activity by its fullname
    $params[] = '%'.$term.'%';

    $sort = 'name';
    $fields = 'name, id';

    // execute the query, and step through them
    $activities = $DB->get_records_sql($sql, $params);
    foreach ($activities as $row) {
        $results[] = array(
            "label"=>$row->name,
            "value"=>$row->id
            );

    }

}

echo json_encode($results);
