<?php
session_start();
include '../function.php';
date_default_timezone_set('Asia/Colombo');
include '../mail.php';
$db = dbConn();

// request send from the login page reset pwd link
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    extract($_POST);

    $reset_token = bin2hex(random_bytes(16));
    $expiration_time = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $sql = "UPDATE users SET Token='$reset_token', TokenExpire='$expiration_time' WHERE Email='$resetEmail'";
    $db->query($sql);

    $sql = "SELECT FirstName FROM users WHERE Email='$resetEmail'";
    $result = $db->query($sql);
    $row = $result->fetch_assoc();
    $firstname = $row['FirstName'];

    $msg = "<h3>RESET Password</h3>";
    $msg .= "<p>Click on the below link to reset your password. The link expires in 1 hour.</p>";
    $msg .= "<a href='http://localhost/photon/web/reset_password.php?token=$reset_token'>Reset password now</a>";
    sendEmail($resetEmail, $firstname, "Reset password", $msg);
    $_SESSION['reset_email'] = 'success';
    header("Location:login.php");
}
