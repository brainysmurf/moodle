<?php

require_once '../../../config.php';
require_once '../lib.php';
require_once '../output.php';

require_login();

setup_page();
output_tabs('Parent');

if (!is_parent($USER->id)) {
    echo 'Only parent accounts can access this section. Contact the DragonNet administrator if you think you should have access.';
    die();
}

# sql query
global $DB;
global $USER;
$family_id = str_replace('P', '', $USER->idnumber);
$family_id = '3158';

$info_string = 'This is a list of enrollments of your children into SSIS Activities.';
$sql = "
select
    usr.id as userid,
    crs.id as course_id,
    cat.path as path,
    bus.bus as bus,
    concat(usr.firstname, ' ', usr.lastname) as child,
    regexp_replace(crs.fullname, '\(.*\)', '') as fullname

from ssismdl_enrol enrl
    join ssismdl_user_enrolments usrenrl
        on usrenrl.enrolid = enrl.id
    join ssismdl_course crs
        on enrl.courseid = crs.id
    join ssismdl_user usr
        on usrenrl.userid = usr.id
    join ssismdl_course_categories cat
        on crs.category = cat.id
    join ssismdl_user_activity_bus bus
        on usr.id = bus.userid
where
    crs.visible = ? and
    usr.idnumber like ? and
    usr.idnumber not like ? and
    enrl.enrol = ?
";
$params = array('1', $family_id.'%', '%P', 'self');

if (!empty($family_id)) {
    $results = $DB->get_recordset_sql($sql, $params);
    if (!empty($results)) {

        echo '<div class="local-alert"><i class="icon-info-sign pull-left icon-2x"></i>'.$info_string.'</div>';
        echo "<br />";

        echo '<table class="userinfotable htmltable" width="100%">';
        echo "<thead>";
        echo '<tr>
        <th class="header c0" style="" scope="col"><p>Child</p></th>
        <th class="header c1" style="" scope="col"><p>Activity</p></th>
        <th class="header c2" style="" scope="col"><p>Bus</p></th>
        <th class="header c2" style="" scope="col"><p>Modifiy Bus Info</p></th>
        </tr>
        </thead>';

        foreach ($results as $item) {
            echo '<tr class="r0">';
            echo '<td class="cell c0"><p>'.$item->child.'</p></td>';
            echo '<td class="cell c1"><p>'.$item->fullname.'</p></td>';
            if ($item->path === '/1/118/122/123' or
                $item->path === '/1/118/122/124' or
                $item->path === '/1/118/122/125') {
                $item_bus_string = ($item->bus === "1" ? "YES" : "NO");
                $item_mod_string = ($item->bus === "1" ? "Change to 'NO'": "Change to 'YES'");
            } else {
                $item_bus_string = "N/A";
                $item_mod_string = "";
            }
            echo '<td class="cell c0"><p>'.$item_bus_string.'</p></td>';
            echo '<td class="cell c1 lastcol"><p><a rel="busmod" href="">'.$item_mod_string.'</a></p></td>';

            echo '</tr>';
        }
        $results->close();

        echo '
<script type="text/javascript">
    $(\'a[rel="busmod"]\').bind("click", function(e) {
        e.preventDefault();
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "../mod_user_bus_activity.php",
            data: {
                userid: '.$item->userid.'
            },
            success: function(data) {
                console.log(data);
                alert("Successfully modified bus information for " + data.name);
            },
            error: function(jqXHR, status) {
                alert("Failed, due to " + jqXHR.statusText);
            },
            async: false
            });
        location.href = "";

    });
</script>';

//         echo '
// <script type="text/javascript">
// $("#busmod").on("click", function () {
//     alert("hi");
// });
// console.log("hi");
// </script>';

    } else {

        echo '<div class="local-alert"><i class="icon-info-sign pull-left icon-2x"></i>This page will display a list of activites your children are enrolled in.</div>';

    }

    echo "<tbody>";

} else {
    echo '<div class="local-alert"><i class="icon-thumbs-down pull-left icon-4x"></i><strong>Apologies, there is something wrong with your account.</strong> <br />Please contact Adam Morris at adammorris@ssis-suzhou.net with the subject header "Message from DragonNet".<br />Be sure to include your DragonNet username. Either that, or your account isn\'t a parent account."</a>"';
}


echo '</tbody></table>';

echo $OUTPUT->footer();

