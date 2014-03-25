<?php

function derive_plugin_path_from($stem) {
    // Moodle really really should be providing a standard way to do this
    return "/local/dnet_syncing/{$stem}";
}

function setup_page($title = false) {
    global $PAGE;
    global $OUTPUT;

    $title = $title ? $title : "DragonNet Destiny Sync";

    $PAGE->set_context(context_system::instance());
    $PAGE->set_url(derive_plugin_path_from('index.php'));
    $PAGE->set_title($title);
    $PAGE->set_heading($title);

    echo $OUTPUT->header();
}
