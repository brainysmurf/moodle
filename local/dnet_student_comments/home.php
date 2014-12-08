<?php

require_once '../../config.php';
require_once 'output.php';
require_once 'portables.php';

require_login();

setup_page();

output_tabs('Teachers');

if (!is_teacher()) {
    death('Only designated Administrators can access this section.');
}

$powerschoolID = optional_param('powerschool', '', PARAM_RAW);
if (!empty($powerschoolID)) {
    $user = $DB->get_record('user', array('idnumber'=>$powerschoolID));
    if (!$user) {
        death('Something is wrong with the accounts associated with powerschool ID '.$powerschoolID.' you need to contact the DragonNet administrator.');
    }
    $family_id = substr($powerschoolID, 0, 4);
}
$save = optional_param('save', '', PARAM_RAW);

if ( empty($powerschoolID) )  {
    output_forms(null, 'Start typing a student\'s name', 'students');
} else {
    echo '<p><a id="confirm" href="'.derive_plugin_path_from('home.php').'" class="btn" id="back"><i class="icon-backward"></i> Back</a>&nbsp;';
    echo '<a id="save" href="#" class="btn"><i class="icon-file"></i> Save</a></p>';

    $table = new html_table();
    $table->attributes['class'] = 'userinfobox';

    $row = new html_table_row();

    $row->cells[0] = new html_table_cell();
    $row->cells[0]->attributes['class'] = 'left side';
    $row->cells[0]->text = $OUTPUT->user_picture($user, array('size' => 100, 'courseid'=>1));

    $row->cells[1] = new html_table_cell();
    $row->cells[1]->attributes['class'] = 'content';
    $row->cells[1]->text = $OUTPUT->container(fullname($user, true), 'username');
    $row->cells[1]->text .= '<table class="userinfotable">';

    // foreach (array('idnumber', 'email') as $field) {
    //     $row->cells[1]->text .= '<tr>
    //         <td>'.get_user_field_name($field).'</td>
    //         <td>'.s($user->{$field}).'</td>
    //     </tr>';
    // }

    $row->cells[1]->text .= '<input autofocus="autofocus" style="width:95%;padding:6px;;" id="comment" />';
    $row->cells[1]->text .= '</table>';

    $table->data = array($row);
    echo html_writer::table($table);
    echo '
<script>
$("#save").on("click", function(e) {
    e.preventDefault();
    console.log("hello");
    $.ajax({
        type: "GET",
        dataType: "json",
        url: "go.php",
        data: {
            save: "YES",
            powerschool: '.$user->idnumber.',
            text: "testing"
        },
        success: function(data) {
            if (data.save == "YES") {
                console.log(data);
            } else {
                alert("Your info did not save. Contact Adam!");
            }
        },
        error: function(jqXHR, status) {
            ("no");
        },
        async: false
        });

});
</script>';

}

echo $OUTPUT->footer();
