<?php

function derive_plugin_path_from($stem) {
    // Moodle really really should be providing a standard way to do this
    return "/local/dnet_activity_center/{$stem}";
}

function setup_page() {
    global $PAGE;
    global $OUTPUT;

    $PAGE->set_context(context_system::instance());
    $PAGE->set_url(derive_plugin_path_from('index.php'));
    $PAGE->set_title("DragonNet Activities Management Center");
    $PAGE->set_heading("DragonNet Activities Management Center");

    echo $OUTPUT->header();
}
