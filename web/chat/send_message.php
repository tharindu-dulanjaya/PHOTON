<?php

session_start();
include '../../function.php';
$db = dbConn();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);
    $user_id = $_SESSION['USERID'];
    $username = $_SESSION['FIRSTNAME'];
    $sql = "INSERT INTO messages (user_id,username, message) VALUES ('$user_id','$username', '$message')";
    $db->query($sql);
}
