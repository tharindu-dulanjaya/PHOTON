<?php
//$db = dbConn();
//$sql = "SELECT * FROM item_category WHERE status=1";
//$result = $db->query($sql);
?>
<footer id="footer">
    <div class="footer-top">
        <div class="container">
            <div class="row">
                <div class="col-3 footer-contact">
                    <a href="index.php"><img src="<?= WEB_URL ?>assets/img/logo1.png" alt="" class="img-fluid" width="100px"></a>
                    <p>
                        71/A <br>
                        Welikadawatta Road<br>
                        Rajagiriya <br><br>
                        <strong>Phone:</strong> 011 20 75 124<br>
                        <strong>Email:</strong> info@photon.lk<br>
                    </p>
                </div>
                <div class="col-3 footer-links">
                    <h4>Our Services</h4>
                    <ul>
                        <li><i class="bx bx-chevron-right"></i> <a href="<?= WEB_URL ?>index.php">Home</a></li>
                        <li><i class="bx bx-chevron-right"></i> <a href="<?= WEB_URL ?>shop.php">Shop</a></li>
                        <li><i class="bx bx-chevron-right"></i> <a href="<?= WEB_URL ?>about_us.php">About Us</a></li>
                        <li><i class="bx bx-chevron-right"></i> <a href="<?= WEB_URL ?>our_services.php">Our Services</a></li>
                    </ul>
                </div>
                <div class="col-3 footer-links">
                    <h4>Our Social Networks</h4>
                    <div class="social-links mt-3">
                        <a href="https://www.youtube.com/@photontechnologies" class="twitter"><i class="bx bxl-youtube"></i></a>
                        <a href="https://web.facebook.com/photonlk" class="facebook"><i class="bx bxl-facebook"></i></a>
                        <a href="https://www.youtube.com/@photontechnologies" class="instagram"><i class="bx bxl-instagram"></i></a>
                        <a href="https://web.facebook.com/photonlk" class="linkedin"><i class="bx bxl-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-3 footer-contact">                   
                    <p>We at PHOTON Technologies commit to introduce a modernized range of products to service the 
                        needs of our valuable clientele. We are proud to say that we have contributed to several major 
                        projects in Sri Lanka in the field of industrial whilst establishing ourselves as a supplier 
                        & service provider par excellence.</p>
                </div>
                
            </div>
        </div>
    </div>
    <div class="container py-4">
        <div class="copyright">
            &copy; Copyright <strong><span>PHOTON</span></strong>. All Rights Reserved
        </div>
        <div class="credits">
            Designed & Developed by TD Senarathne
        </div>
    </div>
</footer>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<script src="<?= WEB_URL ?>assets/js/jquery-3.7.1.min.js" type="text/javascript"></script>
<script src="<?= WEB_URL ?>assets/vendor/aos/aos.js"></script>
<script src="<?= WEB_URL ?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= WEB_URL ?>assets/vendor/glightbox/js/glightbox.min.js"></script>
<script src="<?= WEB_URL ?>assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
<script src="<?= WEB_URL ?>assets/vendor/swiper/swiper-bundle.min.js"></script>

<!-- Template Main JS File -->
<script src="<?= WEB_URL ?>assets/js/main.js"></script>

<!--<script>
function loadItemsByCategory(categoryId) {

        if (categoryId) {

            $.ajax({
                url: 'loadItemsByCategory.php?categoryId=' + categoryId,
                type: 'GET',
                success: function (data) {
                    $("#product_grid").html(data);
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }
    }
</script>-->
