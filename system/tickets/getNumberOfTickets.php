<?php
include '../../function.php';

$db = dbConn();
$sql = "SELECT COUNT(*) AS 'NoOfTickets' FROM tickets";
$result = $db->query($sql);
$row = $result->fetch_assoc();

echo $row['NoOfTickets'];

