<?php

function death($message) {
    echo($message);
    global $OUTPUT;
    echo $OUTPUT->footer();
    die();
}

// This stuff basically manages the permissions and redirecting
function is_admin() {
    if (has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM))) {
        return true;
    }
}

function is_secretary() {
    if (is_admin()) {
        return true;
    }
    global $SESSION;
    return $SESSION->userIsSecretary;
}

function is_teacher() {
    if (is_admin()) {
        return true;
    }
    global $SESSION;
    return $SESSION->userIsTeacher;
}

function is_student() {
    if (is_admin()) {
        return true;
    }
    global $SESSION;
    return $SESSION->userIsStudent;
}

function is_parent() {
    if (is_admin()) {
        return true;
    }
    global $SESSION;
    return $SESSION->userIsParent;
}

function sign($icon, $big_text, $little_text) {
    echo '<div class="local-alert"><i class="icon-4x icon-'.$icon.' pull-left"></i> <p style="font-size:18px;font-weight:bold;">'.$big_text.'</p>'.$little_text.'</div>';
}
