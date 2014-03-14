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
