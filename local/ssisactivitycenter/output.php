<?php
defined('MOODLE_INTERNAL') || die();
require_once 'lib.php';


// Some display stuff
function output_begin_table($message) {
    echo '<div>$message</div><br />';
    echo '<table class="userinfotable htmltable" width="100%"><thead></thead><tbody>';
}

function output_end_table() {
    echo '</tbody></table>';
}

function output_tabs($kind) {
    // output the tabs
    $li = '';
    $kind_array = array("Parent", "Teacher", "Coordinator");
    $size =  count($kind_array);
    for ($i = 0;
        $i < $size;
        ++$i) {
        $label = $kind_array[$i];
        $label_lower = strtolower($label);
        if ($label == $kind) {
            $pre = "<span class=\"selected\">";
            $post = "</span>";
        } else {
            $pre = '<a href="'.derive_plugin_path_from("roles/{$label_lower}".'">');
            $post = "</a>";
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
</div>
    ';
}

function output_forms($user=null) {
    if (!($user)) {
        // user hasn't chosen anybody yet
        $default_words = 'placeholder="Look up by lastname, firstname, or homeroom..." ';
        $powerschoolID = "";
    } else {
        // make sure the the text box displays the right thing, depending on context
        $default_words = 'value="'.$user->firstname.' '.$user->lastname.' ('.$user->department.')" ';
        $powerschoolID = $user->idnumber;
    }
    $path_to_index = derive_plugin_path_from("index.php");
    $path_to_query = "../../ssiscommon/query/students.php";

    echo '
<form name="user_entry" action="'.$path_to_index.'" method="get">
<input name="" autofocus="autofocus" size="100" onclick="this.select()"
    style="font-size:18px;margin-bottom:5px;box-sizing: border-box;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;padding:3px;"
    type="text" id="person" '.$default_words.'/><br />
<input name="powerschool" type="hidden" id="powerschool" value="'.$powerschoolID.'"/>
<input name="action" style="border:4;" type="submit" type="submit" value="view family"/>
<input name="action" type="submit" value="view child"/>
<input name="action" type="submit" value="enrol child"/>
<input name="action" type="submit" value="deenrol child"/>
</form><br />';
    echo '
<script type="text/javascript">
$("#person").autocomplete({
            source: "'.$path_to_query.'",
            select: function (event, ui) {
                event.preventDefault();
                $("#person").val(ui.item.label);
                $("#powerschool").val(ui.item.value);
            },
            change: function (event, ui) {   // TODO: determine if I really really need this
                if (ui != null) {
                    event.preventDefault();
                }
            },
            focus: function (event, ui) {
                event.preventDefault();
                $("#person").val(ui.item.label);
            },
        });
</script>';
}
