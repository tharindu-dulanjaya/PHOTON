<?php
include '../../function.php';

$db = dbConn();

$new_order_flag = false; // assume that no orders initially
$sql = "SELECT * FROM orders WHERE new_order_flag = '1'";
$result = $db->query($sql);

if($result->num_rows > 0){
    $new_order_flag = true;
    $sql = "UPDATE orders SET new_order_flag = '0'";
    $db->query($sql);
}
// send a php variable as it is, through javascript as an array
echo json_encode(array("new_order_flag" => $new_order_flag));
