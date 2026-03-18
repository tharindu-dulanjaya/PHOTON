<?php
include 'header.php';
extract($_GET);
$db = dbConn();

if ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($token)) {
    ?>

    <main id="main">
        <section id="contact" class="contact">
            <div class="container" data-aos="fade-up">
                <div class="row justify-content-center">
                    <?php
                    $sql = "SELECT * FROM users WHERE Token = '$token' AND IsVerified = 0";
                    $result = $db->query($sql);
                    if ($result->num_rows > 0) { // token is verified                       
                        $row = $result->fetch_assoc();
                        $UserId = $row['UserId'];
                        $sql = "UPDATE users SET IsVerified = 1, Token = null WHERE UserId = '$UserId'";
                        $db->query($sql);

                        echo "<script>
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Verification Successful',
                                    showCloseButton: false,
                                    showConfirmButton: false,
                                    confirmButtonText: 'Close',
                                    timer: 2500
                                    });
                                </script>";
                        ?>
                        <h1 class = "text-center text-success">Successful!!</h1>
                        <p class = "text-center mt-3">Your account has been successfully verified. Please log in to access your dashboard.</p>
                        <a href="login.php" class="btn btn-success mt-2" style="width: 20%"> Login</a>
                        <?php
                    } else { // invalid token
                        echo "<script>
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid or Expired token!',
                                showCloseButton: false,
                                showConfirmButton: false,
                                confirmButtonText: 'Close',
                                timer: 2500
                            });
                        </script>";
                        ?>
                        <h1 class = "text-center text-danger">Verification Failed!!</h1>
                        <p class = "text-center mt-3">Please check your email for the correct verification link</p>
                        <a href="register.php" class="btn btn-dark mt-2" style="width: 20%"> Register Now</a>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </section>
    </main>
    <?php
} else {
    header("Location:register.php");
}



include 'footer.php';
