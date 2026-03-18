<?php

include '../../function.php';
$db = dbConn();

$sql = "SELECT * FROM messages ORDER BY timestamp DESC";
$result = $db->query($sql);

$messages = array();
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

// Ensure we return valid JSON
//header('Content-Type: application/json');
echo json_encode($messages);

