<?php
include 'header.php';
?>
<main id="main">
    <!-- Hero Section -->
    <section id="hero" class="d-flex align-items-center">
        <div class="container">
            <div class="row gy-4">
                <div class="col-6 order-2 order-lg-1 d-flex flex-column justify-content-center pb-5" id="hero-left-column">
                    <h1>About Us</h1><br>
                    <h5>Our commitment to innovation and excellence has positioned us as a trusted partner in the industry. 
                        The passion to uplift the quality of life of the nation, has lead us to constantly analyses the demands of technology in the country and bridge them with the world`s best technologies. 
                        Introducing world`s latest state-of-the art technology has driven us to the forefront in the technology arena in Sri Lanka.
                    </h5><br><br>
                </div>
                <div class="col-6 order-1 order-lg-2 hero-img">
                    <img src="<?= WEB_URL ?>assets/img/about us.png" class="animated img-fluid " alt="">
                </div>
            </div>
        </div>
    </section>
    <section id="services" class="services section-bg">
        <div class="container" data-aos="fade-up">

            <div class="section-title">
                <h2></h2>
                <p>Who We Are</p>
            </div>

            <div class="row">
                <div class="col-6 d-flex align-items-stretch" data-aos="zoom-in" data-aos-delay="100">
                    <div class="icon-box">
                        <div class="icon"><i class="bx bx-trophy"></i></div>
                        <h4 class="title"><a href="">Our Value</a></h4>
                        <p class="description">We believe in building long-term relationships with our clients by providing reliable, high-quality products and exceptional customer service. Integrity, innovation, and customer satisfaction are the core values that drive our operations.</p>
                    </div>
                </div>

                <div class="col-6 d-flex align-items-stretch" data-aos="zoom-in" data-aos-delay="200">
                    <div class="icon-box">
                        <div class="icon"><i class="bx bx-microchip"></i></div>
                        <h4 class="title"><a href="">Our Vision</a></h4>
                        <p class="description">Our vision is to transform the industrial landscape of Sri Lanka by introducing advanced technologies and innovative solutions. We aim to be the preferred choice for businesses seeking reliable and efficient industrial products and services.</p>
                    </div>
                </div>
                <!--
                            <div class="col-3 d-flex align-items-stretch" data-aos="zoom-in" data-aos-delay="300">
                                <div class="icon-box">
                                    <div class="icon"><i class="bx bx-wrench"></i></div>
                                    <h4 class="title"><a href="">Exceptional After-Sales Service</a></h4>
                                    <p class="description">We provide comprehensive after-sales service and repairs, ensuring your color sorter machine always performs at its best.</p>
                                </div>
                            </div>
                
                            <div class="col-3 d-flex align-items-stretch" data-aos="zoom-in" data-aos-delay="400">
                                <div class="icon-box">
                                    <div class="icon"><i class="bx bx-file  "></i></div>
                                    <h4 class="title"><a href="">Customized Solutions</a></h4>
                                    <p class="description">Our team works closely with you to provide tailored solutions that meet your specific needs and requirements.</p>
                                </div>
                            </div>-->
            </div>
        </div>
    </section>
</main>
<?php
include 'footer.php';

