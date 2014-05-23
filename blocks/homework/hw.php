<?php

/**
 * Displays a single homework item
 */

define('PUBLIC', true);
require 'include/header.php';

$id = required_param('id', PARAM_INT);

echo $OUTPUT->header();
echo $hwblock->display->tabs();

$hw = \SSIS\HomeworkBlock\HomeworkItem::load($id);

echo $hwblock->display->homeworkList(array($hw));

echo $OUTPUT->footer();
