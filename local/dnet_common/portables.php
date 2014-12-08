<?php

function derive_plugin_path_from($stem) {
    // Moodle really really should be providing a standard way to do this
    return "/local/dnet_reset_passwords/{$stem}";
}
