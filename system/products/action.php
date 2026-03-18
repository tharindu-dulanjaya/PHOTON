<?php
include_once '../init.php';

$db = dbConn();
extract($_GET);

if ($_SERVER['REQUEST_METHOD'] == 'GET' && @$action == 'disable') {    
    $sql = "UPDATE items SET status='2' WHERE id = '$pid'";
    $db->query($sql); 
    header("Location:manage.php");
}
if ($_SERVER['REQUEST_METHOD'] == 'GET' && @$action == 'enable') {
    $sql = "UPDATE items SET status='1' WHERE id = '$pid'";
    $db->query($sql); 
    header("Location:manage.php");
}

