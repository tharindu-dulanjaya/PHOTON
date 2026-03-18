<?php
include '../../function.php';

$db = dbConn();
$sql = "SELECT COUNT(*) AS 'NOOFORDERS' FROM orders";
$result = $db->query($sql);
$row = $result->fetch_assoc();

echo $row['NOOFORDERS'];

