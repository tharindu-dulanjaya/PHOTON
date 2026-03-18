<?php
session_start();
include 'init.php';
$db = dbConn();

extract($_GET); // get token from url
extract($_POST);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>PHOTON | Reset Password</title>
        <link href="<?= SYS_URL ?>assets/dist/img/favicon.png" rel="icon">

        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/plugins/fontawesome-free/css/all.min.css">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/dist/css/adminlte.min.css">
        <link href="<?= SYS_URL ?>assets/dist/css/mystyle.css" rel="stylesheet" type="text/css"/>

        <!-- The sweet alert library should always located above the alert code. Therefore we put this in the header,not in the footer-->
        <script src="<?= WEB_URL ?>assets/js/sweetalert2@11.js" type="text/javascript"></script>
    </head>
    <!--brand green color : #086924-->
    <body class="hold-transition login-page " style="background-color: #343a40;">
        <div class="login-box bg bg-white">
            <div class="login-logo">
                <img src="<?= SYS_URL ?>assets/dist/img/logo1.png" width="100px" class="img-fluid" alt=""/><br>
                <a href="" class="text-black"><b>Reset Password</b></a>
            </div>

            <?php
            // if token is empty, redirect to homepage
            if (empty($token)) {
                header("Location:http://localhost/photon/web/");
            } else {
                // check if the token matches and it is not expired
                $sql = "SELECT * FROM users WHERE Token = '$token' AND TokenExpire > NOW()";
                $result = $db->query($sql);

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $UserId = $row['UserId'];
                } else { // no database records means -> invalid token or token expired
                    echo "<script>
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid or Expired Token!',
                        showCloseButton: false,
                        showConfirmButton: false,
                        confirmButtonText: 'Close',
                        timer: 5000
                        }).then(function() {
                            window.location.href = 'http://localhost/photon/web/';
                        });
                    </script>";
                }
            }

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {                
                // store error messages
                $message = array();
                if (empty($password)) {
                    $message['password'] = "Please enter password!";
                } else {
                    // password strength
                    $uppercase = preg_match('@[A-Z]@', $password);
                    $lowercase = preg_match('@[a-z]@', $password);
                    $number = preg_match('@[0-9]@', $password);
                    $specialChars = preg_match('@[^\w]@', $password);

                    if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
                        $message['password'] = 'Password should be at least 8 characters long, should include at least one uppercase letter, one lowercase letter, one number, and one special character!';
                    } else {
                        if (empty($confirm_password)) {
                            $message['confirm_password'] = "Please confirm your password!";
                        } else {
                            if ($password != $confirm_password) {
                                $message['confirm_password'] = "Passwords do not match!";
                            }
                        }
                    }
                }

                if (empty($message)) {
                    //Use bcrypt hashing algorithem
                    $pw = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET Password='$pw' WHERE UserId='$UserId'";
                    $db->query($sql);

                    echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Changed!',
                        showCloseButton: false,
                        showConfirmButton: false,
                        timer: 3500
                        }).then(function() {
                            window.location.href = 'http://localhost/photon/system/login.php';
                        });
                    </script>";
                }
            }
            ?>
            <div class="card">
                <div class="card-body login-card-body">
                    <p class="login-box-msg">Enter your new password</p>

                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                        <div class="input-group mb-3">
                            <input type="password" class="form-control" name="password" id="password" placeholder="New Password" value="<?= @$password ?>">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm Password">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-5">
                            <input type="hidden" name="UserId" value="<?= $UserId ?>">
                            <input type="hidden" name="token" value="<?= $token ?>">                            
                            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                        </div>
                    </form>
                    <div class="text-danger"><?= @$message['password'] ?></div>
                    <div class="text-danger"><?= @$message['confirm_password'] ?></div>
                </div>
            </div>
        </div>
        <script src="<?= SYS_URL ?>assets/plugins/jquery/jquery.min.js"></script>
        <script src="<?= SYS_URL ?>assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="<?= SYS_URL ?>assets/dist/js/adminlte.min.js"></script>
    </body>
</html>
