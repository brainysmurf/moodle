<?php

$courseid = optional_param('courseid', '', PARAM_RAW);

if (empty($courseid)) {
    output_act_form("Enter the activity", "activities", ENROL);
} else {
    $course = $DB->get_record('course', array('id'=>$courseid));
    if (!empty($SESSION->dnet_activity_center_individuals)) {
        sign("info-sign", "Bulk enrollment",
            "Click the button at the bottom to enroll the below students (and their parents) into the activity {$course->fullname}.");

        foreach (array_reverse($SESSION->dnet_activity_center_individuals) as $individual) {
            $user = $DB->get_record("user", array("idnumber"=>$individual));
            if (!$user) {
                // Could get here if something strange happened ...
                continue;
            }
            user_box($user, $remove=false);
        }

        ?>
        <ul class="buttons">
        <a class="btn" href="?enrol=ENROL&activity_id=<?= $course->id ?> &mode=<?= ENROL ?>"><i class="icon-plus-sign"></i> <?= ENROL.": {$course->fullname}" ?></a>
        </ul>
        <?php

    } else {
        sign("info-sign", "Select some students first", "You need to build a list of students to enrol.");
    }

}
