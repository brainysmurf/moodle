<?php

include 'common_top.php';

if (empty($mode)) {
    $mode = "Browse";
}
output_tabs($mode, array("Browse", "Approve"));



include 'common_end.php';
