<?php
include_once '../init.php';
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    extract($_GET);
    $db = dbConn();
    $sql = "DELETE users, employees FROM users INNER JOIN employees ON users.UserId = employees.UserId WHERE users.UserId = '$userid'";
    $db->query($sql); 
    header("Location:manage.php");
}
