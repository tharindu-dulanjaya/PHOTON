<?php
session_start();
include_once 'init.php';

// if someone is not logged in, the USERID session is not created.If not logged in dashboard cannot be displayed.
if (!isset($_SESSION['USERID'])) {
    header("Location:login.php");
    return; // to stop executing below codes
}
checkAccess('customer'); // only customers should be allowed to view the dashboard
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <title>PHOTON</title>
        <meta content="" name="description">
        <meta content="" name="keywords">
        <link href="<?= WEB_URL ?>assets/img/favicon.png" rel="icon">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,600,600i,700,700i" rel="stylesheet">
        <link href="<?= WEB_URL ?>assets/vendor/aos/aos.css" rel="stylesheet">
        <link href="<?= WEB_URL ?>assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?= WEB_URL ?>assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
        <link href="<?= WEB_URL ?>assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
        <link href="<?= WEB_URL ?>assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
        <link href="<?= WEB_URL ?>assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
        <link href="<?= WEB_URL ?>assets/css/style.css" rel="stylesheet">
        <link href="<?= WEB_URL ?>assets/css/mystyle.css" rel="stylesheet" type="text/css"/>

        <!-- The sweet alert library should always located above the alert code. Therefore we put this in the header,not in the footer-->
        <script src="<?= WEB_URL ?>assets/js/sweetalert2@11.js" type="text/javascript"></script>
    </head>

    <body>
        <header id="header" class="fixed-top d-flex align-items-center my-header-bg">
            <div class="container d-flex align-items-center justify-content-between">
                <div class="logo">
                    <a href="<?= WEB_URL ?>index.php"><img src="<?= WEB_URL ?>assets/img/logo.png" alt="" class="img-fluid"></a>
                </div>
                <nav id="navbar" class="navbar">
                    <ul>
                        <li><a class="mainbutton scrollto" href="<?= WEB_URL ?>dashboard.php" >Welcome, <?= $_SESSION['TITLE'] ?> <?= $_SESSION['FIRSTNAME'] ?></a></li>
                        <li><a class="mainbutton2 scrollto" href="<?= WEB_URL ?>logout.php">Logout</a></li>
                    </ul>
                    <i class="bi bi-list mobile-nav-toggle"></i>
                </nav>
            </div>
        </header>
