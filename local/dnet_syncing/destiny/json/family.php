<?php
require_once dirname(__DIR__) . '/Destiny.php';
$destiny = new Destiny();
$idnumber = $_GET['idnumber'];
$data = $destiny->getFamilyCheckedOutBooks($idnumber);
header('Content-type: application/json');
echo json_encode($data);
