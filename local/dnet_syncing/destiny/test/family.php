<?php

require_once dirname(__DIR__) . '/Destiny.php';
$destiny = new Destiny();

$data = $destiny->getFamilyCheckedOutBooks(3134);

print_r($data);
