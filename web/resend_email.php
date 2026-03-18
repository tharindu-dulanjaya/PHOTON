<?php

session_start();
include '../function.php';
include '../mail.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_SESSION['RNO'])) {
    $db = dbConn();
    $reg_no = $_SESSION['RNO'];
    $sql = "SELECT u.FirstName, u.LastName, u.Email, u.Token from users u INNER JOIN customers c ON c.UserId = u.UserId WHERE c.RegNo = '$reg_no' AND u.IsVerified = 0";
    $result = $db->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $row['FirstName'].' '.$row['LastName'];
        $email = $row['Email'];
        $token = $row['Token'];

        $msg = "<h1>SUCCESSFUL</h1>";
        $msg .= "<h2>Congratulations!!</h2>";
        $msg .= "<p>Your account has been successfully created. Please verify your email using the below link to access the dashboard.</p>";
        $msg .= "<a href='http://localhost/photon/web/verify.php?token=$token'>Click here to verify your account</a>";
        sendEmail($email, $name, "Account Verification", $msg);
        
        // create a session to access from the redirect page, to know that email has been sent
        $_SESSION['email_status'] = 'resent';
        
        header("Location:register_success.php");
        
    }else{ //email is verified (IsVerified = 1)
        
        $_SESSION['email_status'] = 'already_verified';
        header("Location:register_success.php");
    }
}else{
    header("Location:register_success.php");
}

