<?php
define('CLI_SCRIPT', 1);
require dirname(dirname(__DIR__)) . '/config.php';

$q = 'SELECT
ra.id,
crs.id as courseid,
crs.fullname,
ra.roleid,
ra.userid,
usr.email
FROM {role_assignments} ra
JOIN {context} ctx on ctx.id = ra.contextid
JOIN {course} crs on crs.id = ctx.instanceid
JOIN {user} usr on usr.id = ra.userid
where
ctx.contextlevel = 50 -- courses
and ctx.path like \'/1/3/%\' --in the activities category
and usr.email like \'%@student.ssis-suzhou.net\' --students only
and ra.roleid = 3 --with the teacher role
and ra.timemodified >= 1406931576 --enrolled since Fri Aug 1 2014
and crs.id != 1475 -- exclude sports day 2014. not sure whats up with that
';

$rows = $DB->get_records_sql($q);

print_r($rows);
var_dump(count($rows));

foreach ($rows as $row) {
	$DB->execute("UPDATE {role_assignments} SET roleid = 5 WHERE id = {$row->id}");
}
