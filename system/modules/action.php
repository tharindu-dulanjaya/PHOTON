<?php

include_once '../init.php';

$db = dbConn();
extract($_GET);

if ($_SERVER['REQUEST_METHOD'] == 'GET' && @$action == 'disable') {
    $sql = "UPDATE modules SET Status='2' WHERE Id = '$moduleid'";
    $db->query($sql);
    header("Location:manage.php");
}
if ($_SERVER['REQUEST_METHOD'] == 'GET' && @$action == 'enable') {
    $sql = "UPDATE modules SET Status='1' WHERE Id = '$moduleid'";
    $db->query($sql);
    header("Location:manage.php");
}