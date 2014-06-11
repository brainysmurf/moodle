<?php

include 'common_top.php';

if (empty($mode)) {
    $mode = "Browse";
}
output_tabs($mode, array("Browse"));



include 'common_end.php';
