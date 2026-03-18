<?php
include_once '../init.php';

$db = dbConn();
extract($_GET);

if ($_SERVER['REQUEST_METHOD'] == 'GET' && @$action == 'disable') {    
    $sql = "UPDATE suppliers SET Status='2' WHERE SupplierId = '$id'";
    $db->query($sql); 
    header("Location:manage.php");
}
if ($_SERVER['REQUEST_METHOD'] == 'GET' && @$action == 'enable') {
    $sql = "UPDATE suppliers SET Status='1' WHERE SupplierId = '$id'";
    $db->query($sql); 
    header("Location:manage.php");
}

