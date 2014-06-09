<?php

require '../config.php';
require '../src/ClassroomTechTools/AppTemplate/App.php';
$app = new \ClassroomTechTools\AppTemplate\App();

echo $OUTPUT->header();

echo $app->output->tabs();
