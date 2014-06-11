<?php

$courseid = optional_param('courseid', '', PARAM_RAW);

if (empty($courseid)) {
    output_act_form("Enter the activity", "activities", ENROL);
} else {
    $activity = $DB->get_record('course', array('id'=>$courseid));
    if (!empty($SESSION->dnet_activity_center_individuals)) {
        sign("info-sign", "Bulk enrollment",
            "Click the button at the bottom to enroll the below students (and their parents) into the activity {$activity->fullname}.");

        foreach (array_reverse($SESSION->dnet_activity_center_individuals) as $individual) {
            $user = $DB->get_record("user", array("idnumber"=>$individual));
            if (!$user) {
                // Could get here if something strange happened ...
                continue;
            }
            user_box($user, $remove=false);
        }

        $button = '
        <ul class="buttons">
        <a id="bulk_enrol" class="btn" href=""><i class="icon-plus-sign"></i> '.ENROL.': "'.$activity->fullname.'"</a>
        </ul>
        <script>
        $("#bulk_enrol").on("click", function (e) {
            e.preventDefault();
            var formURL = "'.derive_plugin_path_from('activity_mods.php') . '";
            var formData = {
                "enrol": "BULKENROL",
                "activity_id": "'.$activity->id.'",
            };
            $.ajax(
            {
                url : formURL,
                data: formData,
                async: true,
                type: "GET",
                success: function(data, textStatus, jqXHR)
                {
                    alert("Enrolled them!");
                    window.location.reload();
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                    alert("Could not complete the operation for some reason " + textStatus);
                }
            });
        });

        </script>';
        echo $button;

    } else {
        sign("info-sign", "Select some students first", "You need to build a list of students to enrol.");
    }

}
