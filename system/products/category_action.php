<?php
include_once '../init.php';

$db = dbConn();
extract($_GET);

if ($_SERVER['REQUEST_METHOD'] == 'GET' && @$action == 'disable') {    
    $sql = "UPDATE item_category SET status='2' WHERE id = '$catid'";
    $db->query($sql); 
    header("Location:category.php");
}
if ($_SERVER['REQUEST_METHOD'] == 'GET' && @$action == 'enable') {
    $sql = "UPDATE item_category SET status='1' WHERE id = '$catid'";
    $db->query($sql); 
    header("Location:category.php");
}

