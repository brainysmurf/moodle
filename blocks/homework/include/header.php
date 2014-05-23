<?php

require_once dirname(dirname(dirname(__DIR__))) . '/config.php';

require_login();

// Include the goodies for this block
require dirname(__DIR__) . '/HomeworkBlock/Block.php';
$hwblock = new \SSIS\HomeworkBlock\Block;

$PAGE->requires->css('/blocks/homework/assets/bootstrap/css/bootstrap.css');
$PAGE->requires->css('/blocks/homework/assets/css/homework.css');

$PAGE->requires->jquery();
$PAGE->requires->js('/blocks/homework/assets/js/jquery.scrollTo.min.js');
$PAGE->requires->js('/blocks/homework/assets/js/jquery.localScroll.min.js');
$PAGE->requires->js('/blocks/homework/assets/js/jquery.autosize.min.js');
$PAGE->requires->js('/blocks/homework/assets/js/date.js');
$PAGE->requires->js('/blocks/homework/assets/js/homework.js?v=' . time());

$PAGE->set_title(get_string('pagetitle', 'block_homework'));
$PAGE->set_heading(get_string('pagetitle', 'block_homework'));
