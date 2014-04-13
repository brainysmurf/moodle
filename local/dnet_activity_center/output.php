<?php
defined('MOODLE_INTERNAL') || die();
require_once 'portables.php';
require_once 'activities.php';

// Some display stuff
function output_begin_table($message) {
    echo '<div>$message</div><br />';
    echo '<table class="userinfotable htmltable" width="100%"><thead></thead><tbody>';
}

function output_end_table() {
    echo '</tbody></table>';
}

function output_tabs($kind, $tabs, $mode_name="mode") {
    // output the tabs
    $li = '';
    $size =  count($tabs);
    for ($i = 0;$i < $size;++$i) {
        $label = $tabs[$i];
        if ($label == $kind) {
            $pre = "<span class=\"selected\">";
            $post = "</span>";
        } else {
            if ($label==START_AGAIN && is_admin()) {
                $pre = '<a href="'.derive_plugin_path_from("session_mod.php?submode=&value=NO").'">';
                $post = "</a>";
            } else {
                $pre = '<a href="'.derive_plugin_path_from("index.php?".$mode_name."={$label}").'">';
                $post = "</a>";
            }
        }
        $li .= "<li id=\"tab_topic_{$i}\">{$pre}{$label}{$post}</li>";
    }
    echo '
<div class="single-section">
    <div class="tabs">
        <ul>
            '.$li.'
        </ul>
    </div>
</div>';
}

function activity_box($activity, $remove=false) {
    global $OUTPUT;
    global $DB;
    global $CFG;

    $table = new html_table();
    $table->attributes['class'] = 'userinfobox htmltable';
    //$table->attributes['style'] = "width:45%;";   // how to make it show in rows?

    $row = new html_table_row();

    $row->cells[0] = new html_table_cell();
    $row->cells[0]->attributes['class'] = 'left side';
    $icon = $DB->get_record('course_ssis_metadata', array("courseid"=>$activity->id));
    if (!empty($icon)) {
        $row->cells[0]->text = '<i style="margin-left:20px;" class="icon-'.$icon->value.' icon-4x"></i>';
    } else {
        $row->cells[0]->text = "";
    }

    $row->cells[1] = new html_table_cell();
    $row->cells[1]->attributes['class'] = 'content';
    $category = $DB->get_record("course_categories", array("id"=>$activity->category));
    if (empty($category)) {
        $cat_text = "Can't find category!";
    } else {
        $cat_text = 'in '.$category->name;
    }
    $dialog = '<div id="dialog_'.$activity->id.'" title="Rename" style="display:none"> Enter the new name for this activity:
    <form id="dialog_rename_'.$activity->id.'" action="'.derive_plugin_path_from('activity_mods.php').'">
    <input name="activity_id" type="hidden" value="'.$activity->id.'" />
    <input id="dialog_rename_input_'.$activity->id.'" style="width:100%;margin-top:5px;" name="new_name" autofocus="autofocus" size="100" onclick="this.select()" type="text" value="'.$activity->fullname.'" />
    </form>
    .</div>';
    $script = "<script>

    $('#dialog_rename_".$activity->id."').on(\"submit\", function (e) {
        e.preventDefault();
        var formURL = \"".derive_plugin_path_from('activity_mods.php') . "\";
        var formData = {
            \"activity_id\": \"".$activity->id."\",
            \"new_name\": $('#dialog_rename_input_".$activity->id."').val()
        };
        $.ajax(
        {
            url : formURL,
            data: formData,
            async: true,
            type: \"GET\",
            success: function(data, textStatus, jqXHR)
            {
                $('#dialog_".$activity->id."').dialog('close');
                window.location.reload();
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                alert('Could not change the name for some reason... you will have to do it manually (boo!)');
            }
        });
    });

    $('#rename_".$activity->id."').on(\"click\", function(e) {
        e.preventDefault();
        $(\"#dialog_".$activity->id."\").dialog({
            minWidth: 450,
            draggable: false,
            modal: true,
            show: { effect: \"drop\", duration: 400 },
            buttons: [
                {
                    id: 'ok_button_".$activity->id."',
                    text: \"OK\",
                    click: function() {
                        $('#dialog_rename_".$activity->id."').submit();
                    }
                },
            ],
            open: function () {
                $('#ok_button_".$activity->id."').focus();
        }

        });

    });
    </script>";
    $edit_name = '&nbsp;&nbsp;<a id="rename_'.$activity->id.'"   href="#"><i class="icon-cog"></i></a>&nbsp;&nbsp;';
    $row->cells[1]->text = '<div class="username">'.$activity->fullname.$edit_name.' ('. $cat_text.')</div>';
    $row->cells[1]->text .= $dialog.$script;
    $row->cells[1]->text .= '<table class="userinfotable">';

    if ($remove) {
        $row->cells[1]->text .= '<tr>
            <td>Remove from list:</td>
            <td><a href="?mode='.SELECT.'&courseid='.$activity->id.'&remove=YES"><i class="icon-remove"></i></a></td>
        </tr>';
    }
    $row->cells[1]->text .= '<tr>
        <td>Convenient Links:</td>
        <td>
        <a target="_new" href="'.$CFG->wwwroot.'/course/view.php?id='.$activity->id.'"><i class="icon-rocket"></i></a>&nbsp;&nbsp;&nbsp;
        <a target="_new" href="'.$CFG->wwwroot.'/course/edit.php?id='.$activity->id.'"><i class="icon-cogs"></i></a>&nbsp;&nbsp;&nbsp;
        <a target="_new" href="'.$CFG->wwwroot.'/enrol/users.php?id='.$activity->id.'"><i class="icon-user"></i></a>&nbsp;&nbsp;&nbsp;
        </td>
    </tr>';

    # output some basic stats about the activity

    $manager_role = 1;
    $editor_role = 3;
    $participant_role = 5;

    $sql = '
select
   usr.idnumber, usr.firstname, usr.lastname, crs.id
from
   {user_enrolments} usr_enrl
join
   {user} usr
   on
    usr.id = usr_enrl.userid
join
   {enrol} enrl
   on
    usr_enrl.enrolid = enrl.id
join
   {role} rle
   on
    enrl.roleid = rle.id
join
   {course} crs
   on
       enrl.courseid = crs.id
where
    crs.id = ? and
    enrl.roleid = ?
';

    $role_info = array(
        array( "id"=>$manager_role, "name"=>"Managers:" ),
        array( "id"=>$editor_role, "name"=>"Editors:" ),
        array( "id"=>$participant_role, "name"=>"# Participants (incl parents):")
        );
    foreach ($role_info as $role) {
        $params = array($activity->id, $role["id"]);
        $users = $DB->get_records_sql($sql, $params);
        $value = '';
        if (substr($role["name"], 0, 1) == "#") {
            $value = count($users);
        } else {
            foreach ($users as $user) {
                $value .= $user->firstname. ' '. $user->lastname. '   ';
            }
        }
        $row->cells[1]->text .= '<tr>
            <td>'.$role["name"].'</td>
            <td>'.$value.'</td>
        </tr>';
    }

    $row->cells[1]->text .= '</table>';

    $table->data = array($row);
    echo html_writer::table($table);
}

function user_box($user, $remove=false) {
    global $OUTPUT;

    $table = new html_table();
    $table->attributes['class'] = 'userinfobox';
    //$table->attributes['style'] = "width:45%;";   // how to make it show in rows?

    $row = new html_table_row();

    $row->cells[0] = new html_table_cell();
    $row->cells[0]->attributes['class'] = 'left side';
    $row->cells[0]->text = $OUTPUT->user_picture($user, array('size' => 100, 'courseid'=>1));

    $row->cells[1] = new html_table_cell();
    $row->cells[1]->attributes['class'] = 'content';
    $row->cells[1]->text = '<div class="username">'.$user->firstname. ' '. $user->lastname .' ('. $user->department.')</div>';
    $row->cells[1]->text .= '<table class="userinfotable">';

    if ($remove) {
        $row->cells[1]->text .= '<tr>
            <td>Remove from list:</td>
            <td><a href="?mode='.SELECT.'&powerschool='.$user->idnumber.'&remove=YES"><i class="icon-remove"></i></a></td>
        </tr>';
    }

    $activities = get_user_activity_enrollments($user->idnumber);
    if (empty($activities)) {
        $row->cells[1]->text .= '<tr>
            <td>Activities:</td>
            <td>None</td>
        </tr>';
    }
    foreach ($activities as $activity) {
        $row->cells[1]->text .= '<tr>
            <td>Activity:</td>
            <td>'.$activity->fullname.'</td>
        </tr>';
    }

    $row->cells[1]->text .= '</table>';

    $table->data = array($row);
    echo html_writer::table($table);
}

function output_submode_choice($kind, $tabs, $mode_name="mode") {
    // output the tabs
    $li = '';
    $size =  count($tabs);
    for ($i = 0; $i < $size; ++$i) {
        $label = $tabs[$i];
        $label_lower = str_replace(" ", "", strtolower($label));
        switch ($label_lower) {
            case "activities":
                $icon = "rocket";
                break;
            case "individuals":
                $icon = "user";
                break;
            case "becometeacher":
                $icon = "magic";
                break;
        }

        $pre = '<li><a class="btn" href="'.derive_plugin_path_from("session_mod.php?submode=".$label_lower."&value=YES").'">';
        $post = "</a></li>";

        $li .= "{$pre}<i class=\"icon-".$icon."\"></i> {$label}{$post}
";
    }
    echo '
<ul class="buttons">
        '.$li.'
</ul>
';
}

function output_act_form($placeholder="Type something, dude", $kind="activities", $mode="") {
    $path_to_index = "";
    $path_to_query = "../../dnet_common/query/{$kind}.php";

    ?>
<form id="activity_user_entry" action="" method="get">
<input name="" size="100" onclick="this.select()"
    style="width:100%;font-size:18px;margin-bottom:5px;box-sizing: border-box;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;padding:3px;"
    type="text" autofocus="autofocus" id="activity" placeholder="<?= $placeholder ?>"/><br />
<input name="courseid" type="hidden" id="courseid" value=""/>
<input name="mode" type="hidden" id="select" value="<?= $mode ?>"/>
</form><br />
<script>
$("#activity").autocomplete({
            autoFocus: true,
            source: "<?= $path_to_query ?>",
            minLength: 3,
            select: function (event, ui) {
                event.preventDefault();
                $("#activity").val(ui.item.label);
                $("#courseid").val(ui.item.value);
                $("#activity_user_entry").submit();
            },
            change: function (event, ui) {   // TODO: determine if I really really need this
                if (ui != null) {
                    event.preventDefault();
                }
            },
            focus: function (event, ui) {
                event.preventDefault();
            },
        });
</script>
<?php
}

function output_act_cat_form($placeholder="Type something, dude", $kind="students", $mode="") {
    $path_to_index = "";
    $path_to_query = "../../dnet_common/query/{$kind}.php";

    ?>
<form id="cat_user_entry" action="" method="get">
<input name="" size="100" onclick="this.select()"
    style="width:100%;font-size:18px;margin-bottom:5px;box-sizing: border-box;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;padding:3px;"
    type="text" id="activity_cat" placeholder="<?= $placeholder ?>"/><br />
<input name="catid" type="hidden" id="catid" value=""/>
<input name="mode" type="hidden" id="select" value="<?= $mode ?>"/>
</form><br />
<script>
$("#activity_cat").autocomplete({
            autoFocus: true,
            source: "<?= $path_to_query ?>",
            minLength: 3,
            select: function (event, ui) {
                event.preventDefault();
                $("#activity_cat").val(ui.item.label);
                $("#catid").val(ui.item.value);
                $("#cat_user_entry").submit();
            },
            change: function (event, ui) {   // TODO: determine if I really really need this
                if (ui != null) {
                    event.preventDefault();
                }
            },
            focus: function (event, ui) {
                event.preventDefault();
            },
        });
</script>
<?php
}

function output_forms($placeholder="Type something, dude", $kind="students", $mode="") {
    $path_to_index = "";
    $path_to_query = "../../dnet_common/query/{$kind}.php";

    ?>
<form id="user_entry" action="" method="get">
<input name="" autofocus="autofocus" size="100" onclick="this.select()"
    style="width:100%;font-size:18px;margin-bottom:5px;box-sizing: border-box;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;padding:3px;"
    type="text" id="person" placeholder="<?= $placeholder ?>"/><br />
<input name="powerschool" type="hidden" id="powerschool" value=""/>
<input name="mode" type="hidden" id="select" value="<?= $mode ?>"/>
</form><br />
<script>
$("#person").autocomplete({
            autoFocus: true,
            source: "<?= $path_to_query ?>",
            minLength: 3,
            select: function (event, ui) {
                event.preventDefault();
                $("#person").val(ui.item.label);
                $("#powerschool").val(ui.item.value);
                $("#user_entry").submit();
            },
            change: function (event, ui) {   // TODO: determine if I really really need this
                if (ui != null) {
                    event.preventDefault();
                }
            },
            focus: function (event, ui) {
                event.preventDefault();
            },
        });
</script>
<?php
}
