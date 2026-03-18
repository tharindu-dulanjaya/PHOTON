<?php

include_once '../init.php';
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    extract($_GET);
    $db = dbConn();

    if ($uid == 7 && $mid == 1) {
        // admins employee management privilege cannot be removed. then the system would break
    } else {
        $sql = "DELETE FROM user_modules WHERE UserId = '$uid' AND ModuleId = '$mid'";
        $db->query($sql);
    }
    header("Location:privileges.php?userid=$uid");
}
