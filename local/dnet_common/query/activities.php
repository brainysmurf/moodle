<?php

$term = isset($_GET['term']) ? $_GET['term'] : FALSE;
require_once '../../../config.php';
header('Content-type: application/json');

//require_login();

if ($term) {

    // Query is being performed
    $term = str_replace(' ', '', strtolower($term));
    $results = array();
    $params = array();

    $sql = "
SELECT
    crs.fullname, crs.id
FROM
    {course} crs
JOIN
    {course_categories} cat
    ON
        cat.id = crs.category
WHERE
    cat.path like ? AND
    REPLACE(LOWER(fullname), ' ', '') LIKE ?
";

    // query that gets any match of an activity by its fullname
    $params[] = "/1/%";
    $params[] = '%'.$term.'%';

    $sort = 'fullname';
    $fields = 'fullname, id';

    // execute the query, and step through them
    $activities = $DB->get_records_sql($sql, $params);
    foreach ($activities as $row) {
        $results[] = array(
            "label"=>$row->fullname,
            "value"=>$row->id
            );

    }

    echo json_encode($results);
}
