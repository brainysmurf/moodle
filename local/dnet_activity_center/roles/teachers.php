<?php

include 'common_top.php';

if (empty($mode)) {
    $mode = "Browse";
}
output_tabs($mode, array("Browse", "Approve", "Start Over"));



include 'common_end.php';
