<?php
//If a session is not already created, start a session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// if a user is not logged in USERID session is not created. if so, nothing is allowed to access
if (!isset($_SESSION['USERID'])) {
    header("Location:http://localhost/photon/system/login.php");
    return;
}

checkAccess('employee'); // only employees are allowed to view the system dashboard
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>PHOTON | Dashboard</title>

        <link href="<?= SYS_URL ?>assets/dist/img/favicon.png" rel="icon">

        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/plugins/fontawesome-free/css/all.min.css">
        <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/plugins/fullcalendar/main.css">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/plugins/jqvmap/jqvmap.min.css">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/dist/css/adminlte.min.css">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/plugins/daterangepicker/daterangepicker.css">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/plugins/summernote/summernote-bs4.min.css">
        <link rel="stylesheet" href="<?= SYS_URL ?>assets/dist/css/mystyle.css" type="text/css"/>

        <!-- The sweet alert library should always located above the alert code. Therefore we put this in the header,not in the footer-->
        <script src="<?= SYS_URL ?>assets/dist/js/sweetalert2@11.js" type="text/javascript"></script>
    </head>
    <body class="hold-transition sidebar-mini layout-fixed">
        <div class="wrapper">
            <div class="preloader flex-column justify-content-center align-items-center">
                <img class="animation__wobble" src="<?= SYS_URL ?>assets/dist/img/logo1.png" height="150" width="150">
            </div>
            <nav class="main-header navbar navbar-expand navbar-white navbar-light">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="<?= SYS_URL ?>dashboard.php" class="nav-link">Dashboard</a>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="<?= WEB_URL ?>index.php" target="blank" class="nav-link">Website</a>
                    </li>
                </ul>

                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"></li>
                    <li class="nav-item dropdown"></li>
                    <li class="nav-item dropdown"></li>
                    <li class="nav-item">
                        <a class="nav-link"  href="<?= SYS_URL ?>my_account.php" role="button"><i class="fas fa-user"></i> My Account</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"  href="<?= SYS_URL ?>logout.php" role="button"><i class="fas fa-power-off"></i> Logout</a>
                    </li>
                </ul>
            </nav>

            <aside class="main-sidebar sidebar-dark-primary elevation-4">
                <a href="<?= SYS_URL ?>dashboard.php" class="brand-link">
                    <img src="<?= SYS_URL ?>assets/dist/img/favicon.png" alt="PHOTON Logo" class="brand-image img-rounded elevation-3" style="opacity: .8">
                    <span class="brand-text font-weight-normal">PHOTON</span>
                </a>

                <div class="sidebar">
                    <div class="user-panel p-2 d-flex">
                        <span class='badge badge-primary'><?= $_SESSION['DESIGNATION'] ?></span>                      
                    </div>
                    <div class="user-panel mt-2 pb-2 mb-2 d-flex">
                        <div class="info">
                            <a class="d-block"><?= $_SESSION['FIRSTNAME'] . " " . $_SESSION['LASTNAME'] ?></a>
                        </div>                        
                    </div>

                    <?php
                    $userid = $_SESSION['USERID'];
                    $db = dbConn();
                    $sql = "SELECT * FROM user_modules um INNER JOIN modules m ON m.Id = um.ModuleId WHERE um.UserId = '$userid' AND m.Status = '1' ORDER BY Idx ASC";
                    $result = $db->query($sql);

                    // get the current url on the browser url bar
                    $current_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    $url_without_file = preg_replace('/\/[^\/]*$/', '', $current_url);
                    ?>

                    <nav class="mt-2 pb-5">
                        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $menu_url = SYS_URL . $row['Path'] . '/' . $row['File'] . '.php';
                                    $menu_url_without_file = preg_replace('/\/[^\/]*$/', '', $menu_url);

                                    // highlight the selected menu item
                                    $active_class = ($url_without_file == $menu_url_without_file ) ? 'active' : '';

                                    // to keep the sub menu opened, after clicking on a submenu item
                                    $menu_open = ($url_without_file == $menu_url_without_file ) ? 'menu-open' : '';

                                    $module_id = $row['ModuleId'];

                                    // sql query for sub modules
                                    $sql = "SELECT * FROM sub_modules WHERE ModuleId = '$module_id' AND Status = '1' ORDER BY Idx ASC";
                                    $result_sub = $db->query($sql);
                                    if ($result_sub->num_rows > 0) {
                                        ?> 
                                        <li class="nav-item <?= $menu_open ?>"> 
                                            <a href="#" class="nav-link <?= $active_class ?>"> 
                                                <i class="nav-icon <?= $row['Icon'] ?>"></i> 
                                                <p> 
                                                    <!--module name-->
                                                    <?= $row['Name'] ?>   
                                                    <i class="right fas fa-angle-left"></i> 
                                                </p> 
                                            </a> 
                                            <ul class="nav nav-treeview"> 
                                                <?php
                                                $active_class_sub = '';
                                                $url_without_file_sub = preg_replace('/\.[^\/.]+$/', '', $current_url);
                                                while ($row_sub = $result_sub->fetch_assoc()) {
                                                    $menu_url_sub = SYS_URL . $row_sub['Path'] . '/' . $row_sub['File'] . '.php';
                                                    $menu_url_without_file_sub = preg_replace('/\.[^\/.]+$/', '', $menu_url_sub);

                                                    // highlight the selected submenu item
                                                    $active_class_sub = ($url_without_file_sub == $menu_url_without_file_sub ) ? 'active' : '';
                                                    ?> 
                                                    <li class="nav-item">
                                                        <a href="<?= $menu_url_sub ?>" class="nav-link <?= $active_class_sub ?>"> 
                                                            <i class="nav-icon <?= $row_sub['Icon'] ?>"></i> 
                                                            <p> <?= $row_sub['Name'] ?>  </p> 
                                                        </a> 
                                                    </li> 
                                                    <?php
                                                }
                                                ?> 
                                            </ul> 
                                        </li> 
                                        <?php
                                    } else {
                                        // no sub modules for the main module
                                        ?>
                                        <li class="nav-item"> 
                                            <a href="<?= $menu_url ?>" class="nav-link <?= $active_class ?>"> 
                                                <i class="nav-icon <?= $row['Icon'] ?>"></i> 
                                                <p> 
                                                    <?= $row['Name'] ?>                 
                                                </p> 
                                            </a> 
                                        </li>

                                        <?php
                                    }
                                }
                            }
                            ?>
                        </ul>
                    </nav>
                </div>
            </aside>
            <div class="content-wrapper">
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1 class="m-0"><?= @$link ?></h1>
                            </div>
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-right">
                                    <li class="breadcrumb-item"><a href="#"><?= @$breadcrumb_item ?></a></li>
                                    <li class="breadcrumb-item active"><?= @$breadcrumb_item_active ?></li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div> 
                <section class="content">
                    <div class="container-fluid">
                        <?php echo $content ?>
                    </div>
                </section>
            </div>
            <footer class="main-footer">
                <strong>Copyright &copy; 2024 <a href="<?= WEB_URL ?>index.php">PHOTON</a>.</strong>
                All rights reserved.
                <div class="float-right d-none d-sm-inline-block">
                    <b>Developed by</b> W A T D Senarathne
                </div>
            </footer>

            <aside class="control-sidebar control-sidebar-dark">
                <!-- Control sidebar content goes here -->
            </aside>
        </div>

        <!-- jQuery -->
        <script src="<?= SYS_URL ?>assets/plugins/jquery/jquery.min.js"></script>
        <!-- jQuery UI 1.11.4 -->
        <script src="<?= SYS_URL ?>assets/plugins/jquery-ui/jquery-ui.min.js"></script>
        <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
        <script>
            $.widget.bridge('uibutton', $.ui.button)
        </script>
        <!-- Bootstrap 4 -->
        <script src="<?= SYS_URL ?>assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
        <!-- ChartJS -->
        <script src="<?= SYS_URL ?>assets/plugins/chart.js/Chart.min.js"></script>
        <!-- Sparkline -->
        <script src="<?= SYS_URL ?>assets/plugins/sparklines/sparkline.js"></script>
        <!-- JQVMap -->
        <script src="<?= SYS_URL ?>assets/plugins/jqvmap/jquery.vmap.min.js"></script>
        <script src="<?= SYS_URL ?>assets/plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
        <!-- jQuery Knob Chart -->
        <script src="<?= SYS_URL ?>assets/plugins/jquery-knob/jquery.knob.min.js"></script>
        <!-- daterangepicker -->
        <script src="<?= SYS_URL ?>assets/plugins/moment/moment.min.js"></script>
        <script src="<?= SYS_URL ?>assets/plugins/daterangepicker/daterangepicker.js"></script>
        <!-- Tempusdominus Bootstrap 4 -->
        <script src="<?= SYS_URL ?>assets/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
        <!-- Summernote -->
        <script src="<?= SYS_URL ?>assets/plugins/summernote/summernote-bs4.min.js"></script>
        <!-- overlayScrollbars -->
        <script src="<?= SYS_URL ?>assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
        <!-- DataTables  & Plugins -->
        <script src="<?= SYS_URL ?>assets/plugins/datatables/jquery.dataTables.min.js"></script>
        <script src="<?= SYS_URL ?>assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
        <script src="<?= SYS_URL ?>assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
        <script src="<?= SYS_URL ?>assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
        <script src="<?= SYS_URL ?>assets/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
        <script src="<?= SYS_URL ?>assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
        <script src="<?= SYS_URL ?>assets/plugins/jszip/jszip.min.js"></script>
        <script src="<?= SYS_URL ?>assets/plugins/pdfmake/pdfmake.min.js"></script>
        <script src="<?= SYS_URL ?>assets/plugins/pdfmake/vfs_fonts.js"></script>
        <script src="<?= SYS_URL ?>assets/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
        <script src="<?= SYS_URL ?>assets/plugins/datatables-buttons/js/buttons.print.min.js"></script>
        <script src="<?= SYS_URL ?>assets/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
        <!-- AdminLTE App -->
        <script src="<?= SYS_URL ?>assets/dist/js/adminlte.js"></script>
        <!-- AdminLTE App -->
        <!--<script src="<?= SYS_URL ?>assets/dist/js/adminlte.min.js"></script>-->
        <!-- fullCalendar 2.2.5 -->
        <!--<script src="<?= SYS_URL ?>assets/plugins/moment/moment.min.js"></script>-->
        <script src="<?= SYS_URL ?>assets/plugins/fullcalendar/main.js"></script>
        <!-- AdminLTE for demo purposes -->
        <!--<script src="<?= SYS_URL ?>assets/dist/js/demo.js"></script>-->
        <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
        <script src="<?= SYS_URL ?>assets/dist/js/pages/dashboard.js"></script>
    </body>
</html>