<?php
output_forms(PLACEHOLDER, "students", SELECT);
if (!empty($powerschool)) {

    $remove = optional_param('remove', 'NO', PARAM_RAW);

    if ($remove=="YES") {
        $ref = array_search($powerschool, $SESSION->dnet_activity_center_individuals);
        if (!($ref === false)) {
            unset($SESSION->dnet_activity_center_individuals[$ref]);
        }
    } else {
        if (empty($SESSION->dnet_activity_center_individuals)) {
            $SESSION->dnet_activity_center_individuals = array();
        }
        if (!in_array($powerschool, $SESSION->dnet_activity_center_individuals)) {
            $SESSION->dnet_activity_center_individuals[] = $powerschool;
        }
    }
}

if (!empty($SESSION->dnet_activity_center_individuals)) {
    foreach (array_reverse($SESSION->dnet_activity_center_individuals) as $individual) {
        $user = $DB->get_record("user", array("idnumber"=>$individual));
        if (!$user) {
            // Could get here if something strange happened ...
            continue;
        }

        user_box($user, $remove=true);

    }

    ?>
    <ul class="buttons">
    <a class="btn" href="?mode=<?= CLEAR ?>"><i class="icon-remove"></i> <?= CLEAR ?></a>
    </ul>
    <?php
}
