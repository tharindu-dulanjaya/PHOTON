<?php
include '../../function.php';

$db = dbConn();
$sql = "SELECT COUNT(*) AS 'NoOfCustomers' FROM customers";
$result = $db->query($sql);
$row = $result->fetch_assoc();

echo $row['NoOfCustomers'];

