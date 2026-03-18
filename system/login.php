<?php
session_start();
include 'init.php';

// if already logged in, can not allow to access login page again
if (isset($_SESSION['USERID'])) {
    header("Location:http://localhost/photon/system/logout.php");
}
if (isset($_SESSION['reset_email']) && $_SESSION['reset_email'] == 'success') {
    echo "<script>
                    Swal.fire({
                        icon: 'info',
                        title: 'Reset link sent to your email!',
                        showCloseButton: false,
                        showConfirmButton: false,
                        timer: 3500
                        });
                    </script>";
    unset($_SESSION['reset_email']);
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>PHOTON | Log in</title>
        <link href="<?= SYS_URL ?>assets/dist/img/favicon.png" rel="icon">

        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/plugins/fontawesome-free/css/all.min.css">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/dist/css/adminlte.min.css">
        <link href="<?= SYS_URL ?>assets/dist/css/mystyle.css" rel="stylesheet" type="text/css"/>
    </head>
    <body class="hold-transition login-page " style="background-color: #086924;">
        <div class="login-box bg bg-white">
            <div class="login-logo">
                <img src="<?= SYS_URL ?>assets/dist/img/logo1.png" width="100px" class="img-fluid" alt=""/><br>
                <a href="" class="text-black"><b>System Login</b></a>
            </div>

            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                extract($_POST);
                $username = dataClean($username);
                //To store error messages
                $message = array();

                if (empty($username)) {
                    $message['username'] = "Username can not be empty!";
                }
                if (empty($password)) {
                    $message['password'] = "Password can not be empty!";
                }

                if (empty($message)) {
                    $db = dbConn();
                    $sql = "SELECT * FROM users u "
                            . "INNER JOIN employees e ON e.UserId=u.UserId "
                            . "INNER JOIN designations d ON d.Id=e.DesignationId "
                            . "LEFT JOIN titles t ON u.TitleId = t.Id "
                            . "WHERE u.UserName='$username' AND u.UserType='employee' AND u.Status='1'";
                    $result = $db->query($sql);

                    if ($result->num_rows == 1) {  // if true, there is an actual user
                        $row = $result->fetch_assoc(); //no while loop is used bcz only one row
                        //inbuilt function to verify pwd
                        if (password_verify($password, $row['Password'])) {
                            $_SESSION['USERID'] = $row['UserId'];
                            $_SESSION['TITLE'] = $row['Title'];
                            $_SESSION['FIRSTNAME'] = $row['FirstName'];
                            $_SESSION['LASTNAME'] = $row['LastName'];
                            $_SESSION['DESIGNATIONID'] = $row['DesignationId']; // to access in the dashboard
                            $_SESSION['DESIGNATION'] = $row['Designation'];

                            header("Location:dashboard.php");
                        } else {
                            $message['password'] = "Invalid Username or Password!";
                        }
                    } else {
                        $message['password'] = "Invalid Username or Password!";
                    }
                }
            }
            ?>

            <div class="card">
                <div class="card-body login-card-body">
                    <p class="login-box-msg">Sign in to start your session</p>

                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" name="username" id="username" placeholder="Username">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-5">
                            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                        </div>
                    </form>
                    <div class="text-danger"><?= @$message['username'] ?></div>
                    <div class="text-danger"><?= @$message['password'] ?></div>
                </div>
                <div class="form-check mt-1 mb-2 text-center">
                    <input class="form-check-input" type="checkbox" value="" id="forgotPasswordCheck">
                    <label class="form-check-label" for="forgotPasswordCheck" style="cursor: pointer">Forgot Password?</label>
                </div>
                <div id="forgotPasswordSection" class="col-12 mt-2 mb-3" style="display: none;">
                    <form method="POST" action="reset_request.php">
                        <div class="row">
                            <div class="col-9">
                                <input type="email" class="form-control" name="resetEmail" id="resetEmail" placeholder="Your Email Address">
                            </div>
                            <div class="col-3">
                                <button type="submit" class="btn btn-outline-dark"> Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="<?= SYS_URL ?>assets/plugins/jquery/jquery.min.js"></script>
        <script src="<?= SYS_URL ?>assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="<?= SYS_URL ?>assets/dist/js/adminlte.min.js"></script>
    </body>
</html>

<script>
    $(document).ready(function () {
        $('#forgotPasswordCheck').change(function () {
            if (this.checked) {
                $('#forgotPasswordSection').show();
            } else {
                $('#forgotPasswordSection').hide();
            }
        });
    });
</script>