<?php

require_once dirname(__DIR__) . '/Destiny.php';
$destiny = new Destiny();

$data = $destiny->getUsersCheckedOutBooks(31342);

print_r($data);
