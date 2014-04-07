<?php

$powerschool = optional_param('powerschool', '', PARAM_RAW);

switch ($mode) {

    case BROWSE:
        output_forms(PLACEHOLDER, "students", BROWSE);
        if (!empty($powerschool)) {
            $where = "idnumber like ?";
            $familyid = substr($powerschool, 0, 4);
            $params = array($familyid.'%');
            $users = $DB->get_records_select("user", $where, $params);
            if (empty($users)) {
                sign("thumbs-down", "No such users", "Could not find anything!");
                death("");
            }
            foreach ($users as $user) {
                user_box($user);
            }
        }
        break;

    case CLEAR:
        unset($SESSION->dnet_activity_center_individuals);
        sign("thumbs-up", "List cleared.", "Go to ".SELECT." to start building a new list.");
        break;

    case SELECT:
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

        break;

    case ENROL:
        output_forms("Enter something", "activities", SELECT);
        break;

}
