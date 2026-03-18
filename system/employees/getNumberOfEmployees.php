<?php
include '../../function.php';

$db = dbConn();
$sql = "SELECT COUNT(*) AS 'NoOfEmployees' FROM employees";
$result = $db->query($sql);
$row = $result->fetch_assoc();

echo $row['NoOfEmployees'];

