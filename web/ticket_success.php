<?php
include 'header.php';
?>

<script>
Swal.fire({
  position: "top-end",
  icon: "success",
  title: "Ticket Created",
  showConfirmButton: false,
  timer: 2500
});
</script>

<main id="main">
    <section id="contact" class="contact">
        <div class="container" data-aos="fade-up">
            <div class="section-title">
                <h2></h2>                
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-7 border border-3 border-success" data-aos="fade-up" data-aos-delay="200">
                    <h1 class="text-center text-success">SUCCESSFUL</h1>
                    <p class="text-center">Your ticket has been successfully created.</p>
                    <h2 class="text-center"><b>Your Ticket Number is <?= $_SESSION['TNO'] ?></b></h2> 
                </div>
            </div>
        </div>
    </section>
</main>

<?php
include 'footer.php';
?>

