<?php

sign("info-sign", "Bulk de-enrollment",
    "Click to de-enrol all indicated users.");

$rows = array();

$sql = "
SELECT
    concat(usr.firstname, ' ', usr.lastname) as username, usr.id as userid, crs.fullname as course_name, crs.id as courseid, ccat.name category
FROM
    {user} usr
JOIN
    {user_enrolments} usrenrl
ON
    usrenrl.userid = usr.id
JOIN
    {enrol} enrl
ON
    enrl.id = usrenrl.enrolid
JOIN
    {course} crs
ON
    crs.id = enrl.courseid
JOIN
    {course_categories} ccat
ON
    ccat.id = crs.category
WHERE
    usr.id = ? and
    ccat.path like ?
";

// first go through the individuals that have been built in the list part
$activity_groupings = array();

foreach ($SESSION->dnet_activity_center_individuals as $individual) {
    $user = $DB->get_record("user", array("idnumber"=>$individual));
    if (!$user) {
        // Could get here if something strange happened ...
        continue;
    }

    $params = array($user->id, '/1/%');
    foreach ($DB->get_records_sql($sql, $params) as $row) {
        $activities_groupings[$row->courseid][] = $row;
    }
}

$table = new html_table();
$table->attributes['class'] = 'userinfotable';
$rows = array();
$forms_and_scripts = '';
foreach ($activities_groupings as $activity_key=>$activity_list) {
    $row = new html_table_row();
    $row->cells[0] = new html_table_cell();
    $row->cells[0]->text = '<a id="click_'.$activity_key.'" href=""><i class="icon-minus-sign"></i></a>';
    $row->cells[1] = new html_table_cell();
    $row->cells[1]->text = '';
    $row->cells[2] = new html_table_cell();

    $forms_and_scripts .= '<form id="'.$activity_key.'">
    <input id="courseid_'.$activity_key.'" name="courseid" type="hidden" value="'.$activity_key.'" />
    ';

    foreach ($activity_list as $activity) {
        $row->cells[1]->text = $activity->course_name;
        $row->cells[2]->text .= '&nbsp;&nbsp;' . $activity->username;
        $forms_and_scripts .= '<input class="userid" type="hidden" value="'.$activity->userid.'" /></form>';
        $forms_and_scripts .= "<script>
        $('#".$activity_key."').on('submit', function(e) {
            e.preventDefault();
            formURL = \"".derive_plugin_path_from('activity_mod.php')."\";
            formData = {
                'userids': $('.userid'),
                'courseid': $('#courseid_'.concat(".$activity_key."))
            };
            $.ajax({
                url : formURL,
                data: formData,
                async: true,
                type: 'GET',
                success: function(data, textStatus, jqXHR)
                {
                    alert('success!');
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                    alert('fail!');
                }
            });

        });

        $('#click_'.concat(".$activity_key.")).on('click', function(e) {
            $('#'.concat(".$activity_key.")).submit();
        });
        </script>";

    }
    $rows[] = $row;
}
$table->data = $rows;
echo html_writer::table($table);
echo $forms_and_scripts;
