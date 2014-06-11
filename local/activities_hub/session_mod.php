<?php

require_once '../../config.php';
require_once 'portables.php';

$submode = required_param('submode', PARAM_RAW);
$value = required_param('value', PARAM_RAW);

if ($value=="YES") {

    $SESSION->dnet_activity_center_submode = $submode;

} else if ($value=="NO") {

    unset($SESSION->dnet_activity_center_submode);

}

redirect(derive_plugin_path_from(''));
