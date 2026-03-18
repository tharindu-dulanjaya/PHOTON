<?php
ob_start(); //output buffer start. enables to pass multiple headers in the page
include 'header.php'; // init.php included in header

if(isset($_SESSION['reset_email']) && $_SESSION['reset_email']=='success'){
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
<main id="main">
    <section id="contact" class="contact">
        <div class="container " data-aos="fade-up">
            <div class="section-title">
                <h2>Customer</h2>
                <p>Login</p>
            </div>
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                extract($_POST);
                $username = dataClean($username);
                //To store error messages
                $message = array();

                //Required validations
                if (empty($username)) {
                    $message['username'] = "Please enter your username";
                }
                if (empty($password)) {
                    $message['password'] = "Please enter your password";
                }
                if (empty($message)) {
                    $db = dbConn();
                    // account email address has to verified before allowing to login
                    $sql = "SELECT * FROM users u "
                            . "INNER JOIN customers c ON c.UserId=u.UserId "
                            . "LEFT JOIN titles t ON u.TitleId = t.Id "
                            . "WHERE u.UserName='$username' "
                            . "AND u.UserType='customer' "
                            . "AND u.Status='1' "
                            . "AND u.IsVerified='1'";
                    $result = $db->query($sql);
                    if ($result->num_rows == 1) {  // if true, there is an actual user
                        $row = $result->fetch_assoc(); //no while loop is used bcz only one row
                        //inbuilt function to verify pwd
                        if (password_verify($password, $row['Password'])) {
                            $_SESSION['USERID'] = $row['UserId'];
                            $_SESSION['TITLE'] = $row['Title'];
                            $_SESSION['FIRSTNAME'] = $row['FirstName'];
                            $_SESSION['LASTNAME'] = $row['LastName'];
                            header("Location:dashboard.php");
                        } else {
                            $message['password'] = "Invalid Username or Password! <br>(Please make sure that you have verified your email address)";
                        }
                    } else {
                        $message['password'] = "Invalid Username or Password! <br>(Please make sure that you have verified your email address)";
                    }
                }
            }
            ?>
            <div class="row justify-content-center">
                <div class="col-md-4 mt-5 d-flex align-items-stretch " data-aos="fade-up" data-aos-delay="200">
                    <!--Add novalidate to skip browser validations-->
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" role="form" class="php-email-form " novalidate>
                        <div class="form-floating mt-3">                            
                            <input type="text" class="form-control" name="username" id="username" placeholder="Enter your Username" required>
                            <label for="username">User Name</label>
                            <span class="error_span text-danger"><?= @$message['username'] ?></span>
                        </div>
                        <div class="form-floating mt-3 mb-3">                            
                            <input type="password" class="form-control" name="password" id="password" placeholder="Enter your Password" required>
                            <label for="password">Password</label>
                            <span class="error_span text-danger"><?= @$message['password'] ?></span>
                        </div>
                        <div class="text-center"><button type="submit">Login</button></div>
                    </form>
                </div>

                <div class="form-check mt-1 mb-2 text-center">
                    <input class="form-check-input" type="checkbox" value="" id="forgotPasswordCheck">
                    <label class="form-check-label" for="forgotPasswordCheck" style="cursor: pointer">Forgot Password?</label>
                </div>
                <div id="forgotPasswordSection" class="col-4 mt-2 mb-3" style="display: none;">
                    <form method="POST" action="reset_request.php">
                        <div class="row">
                            <div class="col-8">
                                <input type="email" class="form-control" name="resetEmail" id="resetEmail" placeholder="Your Email Address">
                            </div>
                            <div class="col-4">
                                <button type="submit" class="btn btn-outline-dark"> Send Link</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>
<?php
include 'footer.php';
ob_end_flush(); //must end flush the function at the very end if we use ob_start at the top.
?>  

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