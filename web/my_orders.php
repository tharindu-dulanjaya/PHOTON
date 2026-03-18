<?php
ob_start();
include 'dashboard_header.php';
$db = dbConn();
?>

<main id="main">
    <!-- Breadcrumbs Section-->
    <section class="breadcrumbs">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h2>My Machines</h2>
                <ol>
                    <li><b>Customer</b></li>
                    <li><a href="<?= WEB_URL ?>dashboard.php" style="color: #fff;">Dashboard</a></li>
                    <li>My Orders</li>
                </ol>
            </div>
        </div>
    </section>

    <section id="portfolio-details" class="portfolio-details">
        <div class="container">
            <?php
            extract($_POST);
            // cancel order
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'cancel') {
                $sql = "UPDATE orders SET order_status='7' WHERE id='$oid'";
                $db->query($sql);
            }

            // this session is already created if the customer is in the dashboard
            if (isset($_SESSION['USERID'])) {
                $userid = $_SESSION['USERID'];

                //find the customer_id from the user_id
                $sql = "SELECT * FROM customers WHERE UserId = '$userid'";
                $result = $db->query($sql);
                $row = $result->fetch_assoc();
                $customer_id = $row['CustomerId'];

                // display the purchased machines that are in initial stage before issueing(status 4) and cancelled(7)
                $sql = "SELECT * FROM orders o "
                        . "INNER JOIN order_items oi ON oi.order_id=o.id "
                        . "INNER JOIN items i ON i.id=oi.item_id "
                        . "INNER JOIN item_category ic ON ic.id=i.item_category "
                        . "INNER JOIN order_status os ON os.StatusId=o.order_status "
                        . "WHERE o.customer_id='$customer_id' AND (o.order_status<4 OR o.order_status=7) "
                        . "ORDER BY o.order_number DESC";
                $result = $db->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <div class="row mt-5 border-bottom">
                            <div class="col-lg-3">
                                <img src="../uploads/products/<?= $row['item_image'] ?>" width="100%"/> 
                            </div>
                            <div class="col-lg-3">
                                <div class="portfolio-info">
                                    <h3>Machine Details</h3>
                                    <ul>
                                        <li><strong>Category</strong>: <?= $row['category_name'] ?></li>
                                        <li><strong>Machine</strong>: <?= $row['item_name'] ?></li>
                                        <li><strong>Model No.</strong>: <?= $row['model_no'] ?></li>
                                        <li><strong>Quantity</strong>: <?= $row['qty'] ?></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="portfolio-info">
                                    <h3>Order Information</h3>
                                    <ul>
                                        <li><strong>Order Number</strong>: <?= $row['order_number'] ?></li>
                                        <li><strong>Order Status</strong>: <span class="
                                            <?php
                                            if ($row['order_status'] == '1') { // pending
                                                echo 'badge rounded-pill text-bg-warning';
                                            } elseif ($row['order_status'] == '2') { // failed payment
                                                echo 'badge rounded-pill text-bg-danger';
                                            } elseif ($row['order_status'] == '3') { // payment approved
                                                echo 'badge rounded-pill text-bg-info';
                                            } else {
                                                echo 'badge rounded-pill text-bg-danger';
                                            }
                                            ?>
                                                                                 "><?= $row['OrderStatus'] ?></span></li>

                                        <li><strong>Purchased Date</strong>: <?= $row['order_date'] ?></li>
                                        <li><strong>Purchased Price</strong>: <?= $row['unit_price'] ?></li>
                                        <?php
                                        if ($row['order_status'] == '2') { // failed payment
                                            ?>
                                            <li>
                                                <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
                                                    <input type="file" class="form-control" name="slip_upload" id="slip_upload">
                                                    <input type="hidden" name="oid" value="<?= $row['order_id'] ?>">
                                                    <input type="hidden" name="action" value="upload_slip">
                                                    <button type="submit" class="btn btn-sm btn-warning float-right mt-2"><i class="fa fa-ban"></i> Upload slip again</button> 
                                                    <div class="error_span text-danger mt-4"><?= @$message['payment_method'] ?></div><br>
                                                </form>
                                            </li>
                                            <?php
                                        } else {
                                            
                                        }
                                        ?>  
                                    </ul>
                                </div>
                            </div>

                            <?php
                            // if cancelled, hide below section
                            if ($row['order_status'] != 7) { // 7 means cancelled
                                ?>
                                <div class="col-lg-3">
                                    <div class="portfolio-info">
                                        <ul>
                                            <li>You can cancel this order by yourself, before the items are issued.</li>
                                        </ul>
                                        <!--this form will not be displayed when the items are issued (whole query is not execued)-->
                                        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                            <input type="hidden" name="oid" value="<?= $row['order_id'] ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-ban"></i> Cancer Order</button> 
                                        </form>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                }

                // query to retrieve purchased machines (that are already issued)
                $sql = "SELECT * FROM orders o "
                        . "INNER JOIN order_items_issue oii ON oii.order_id=o.id "
                        . "INNER JOIN issued_serial_numbers isn ON isn.Order_Items_Issue_Id=oii.id "
                        . "INNER JOIN items i ON i.id=oii.item_id "
                        . "INNER JOIN item_category ic ON ic.id=i.item_category "
                        . "INNER JOIN order_status os ON os.StatusId=o.order_status "
                        . "WHERE o.customer_id='$customer_id' "
                        . "ORDER BY o.order_number DESC";
                $result = $db->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <div class="row mt-5 border-bottom">
                            <div class="col-lg-3">
                                <img src="../uploads/products/<?= $row['item_image'] ?>" width="100%"/> 
                            </div>
                            <div class="col-lg-3">
                                <div class="portfolio-info">
                                    <h3>Machine Details</h3>
                                    <ul>
                                        <li><strong>Category</strong>: <?= $row['category_name'] ?></li>
                                        <li><strong>Machine</strong>: <?= $row['item_name'] ?></li>
                                        <li><strong>Model No.</strong>: <?= $row['model_no'] ?></li>
                                        <li><strong>Serial No.</strong>: <?= $row['SerialNo'] ?></li>   
                                        <?php
                                        $sno = $row['SerialNo'];
                                        $sql5 = "SELECT WarrantyExpiryDate FROM issued_serial_numbers WHERE SerialNo='$sno'";
                                        $result5 = $db->query($sql5);
                                        if ($result5->num_rows > 0) {
                                            $row5 = $result5->fetch_assoc();
                                            $warr = $row5['WarrantyExpiryDate'];
                                            ?>
                                            <li class="text-danger"><strong>Warranty Expire On.</strong>: <?= $warr ?></li>  
                                            <?php
                                        }
                                        ?>

                                    </ul>
                                    <?php
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="portfolio-info">
                                    <h3>Order Information</h3>
                                    <ul>
                                        <li><strong>Order Number</strong>: <?= $row['order_number'] ?></li>
                                        <li><strong>Order Status</strong>: <span class="
                                            <?php
                                            if ($row['order_status'] == '1') {
                                                echo 'badge rounded-pill text-bg-warning';
                                            } elseif ($row['order_status'] == '2') {
                                                echo 'badge rounded-pill text-bg-danger';
                                            } elseif ($row['order_status'] == '3') {
                                                echo 'badge rounded-pill text-bg-info';
                                            } elseif ($row['order_status'] == '4') {
                                                echo 'badge rounded-pill text-bg-primary';
                                            } elseif ($row['order_status'] == '5') {
                                                echo 'badge rounded-pill text-bg-dark';
                                            } else {
                                                echo 'badge rounded-pill text-bg-success';
                                            }
                                            ?>
                                                                                 "><?= $row['OrderStatus'] ?></span></li>
                                        <li><strong>Purchased Date</strong>: <?= $row['order_date'] ?></li>
                                        <li><strong>Purchased Price</strong>: <?= $row['unit_price'] ?></li>
                                        <li><strong>Delivered On</strong>: <?= $row['delivered_on'] ?></li>
                                        <li><strong>Installed On</strong>: <?= $row['installed_on'] ?></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="portfolio-info">
                                    <h3>QR on machine</h3>
                                    <ul>
                                        <li><img src="../qr/<?= $row['QR_Image'] ?>" width="150px"/> </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
            } else { // session user_id is not set. redirect to login page
                header("Location:login.php");
            }
            ?>
        </div>
    </section>
</main>

<?php
include 'dashboard_footer.php';
ob_end_flush();
?>
