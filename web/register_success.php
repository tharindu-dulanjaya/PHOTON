<?php
include 'header.php';
?>

<script>
    Swal.fire({

        icon: "success",
        title: "Registration Completed",
        showConfirmButton: false,
        timer: 2500
    });
</script>


<main id="main">
    <section id="contact" class="contact">
        <div class="container" data-aos="fade-up">
            <div class="row justify-content-center">
                <div class="col-lg-7 border-success" data-aos="fade-up" data-aos-delay="200">
                    <h1 class="text-center text-success">Congratulations!!</h1>
                    <p class="text-center text-success">Your account has been successfully created.</p>
                    <?php
                    if (isset($_SESSION['RNO'])) {
                        ?>
                        <h2 class="text-center">Your Registration Number is <?= $_SESSION['RNO'] ?></h2>
                        <?php
                    }
                    ?>

                    <p class="text-center">We have sent a verification link to your email address. Please verify your email to access the dashboard.<br><br>
                        Didn't receive the email?<br>
                        <a href='resend_email.php'>Resend</a></p>
                </div>
            </div>
        </div>
    </section>
</main>


<?php
// when clicked on resend email
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    // email has sent again successfully
    if (isset($_SESSION['email_status']) && $_SESSION['email_status'] == 'resent') {
        echo "<script>
                            Swal.fire({
                                icon: 'info',
                                title: 'New Email has been sent!',
                                showCloseButton: false,
                                showConfirmButton: false,
                                confirmButtonText: 'Close',
                                timer: 2500
                            });
                        </script>";
    }
    if (isset($_SESSION['email_status']) && $_SESSION['email_status'] == 'already_verified') {
        echo "<script>
                            Swal.fire({
                                icon: 'success',
                                title: 'Your Email address is already verified!',
                                showCloseButton: false,
                                showConfirmButton: false,
                                confirmButtonText: 'Close',
                                timer: 2500
                            });
                        </script>";
    }
}

include 'footer.php';

