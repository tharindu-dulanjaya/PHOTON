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
                <h2>Payment History</h2>
                <ol>
                    <li><b>Customer</b></li>
                    <li><a href="<?= WEB_URL ?>dashboard.php" style="color: #fff;">Dashboard</a></li>
                    <li>Payments</li>
                </ol>
            </div>
        </div>
    </section>

    <section id="portfolio-details" class="portfolio-details">
        <div class="container">
            <?php
            extract($_POST);

            // upload slip again for 'payment failed' orders
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'upload_slip') {
                $message = array();
                if (empty($_FILES['slip_upload']['name'])) {
                    $message['payment_method'] = "Please upload the bank transfer slip!";
                }
                //payment slip upload
                if (!empty($_FILES['slip_upload']['name'])) {
                    $file = $_FILES['slip_upload'];
                    $location = "../uploads/payments";
                    $uploadResult = uploadFile($file, $location);
                    if ($uploadResult['upload']) {
                        $bankSlip = $uploadResult['file'];
                    } else {
                        $error = $uploadResult['error_file'];
                        $message['payment_method'] = "<br>Bank Slip upload failed : $error";
                    }
                }
                if (empty($message)) {
                    $sql = "UPDATE orders SET payment_slip='$bankSlip' WHERE id='$oid'";
                    $db->query($sql);
                    echo "<script>
                                        Swal.fire({
                                            icon: 'info',
                                            title: '',
                                            html: '<h4>Payment slip uploaded!</h4>',
                                            showCloseButton: false,
                                            showConfirmButton: false,
                                            timer: 2500
                                        });
                                      </script>";
                }
            }

            // this session is already created if the customer is in the dashboard
            if (isset($_SESSION['USERID'])) {
                $userid = $_SESSION['USERID'];

                //find the customer_id from the user_id
                $sql = "SELECT * FROM customers WHERE UserId = '$userid'";
                $result = $db->query($sql);
                $row = $result->fetch_assoc();
                $customer_id = $row['CustomerId'];

                // query to retrieve order details & payments
                $sql = "SELECT o.id,o.order_number,o.order_date,o.order_status,o.slip_rejected,os.OrderStatus FROM orders o "
                        . "INNER JOIN order_status os ON os.StatusId=o.order_status WHERE o.customer_id='$customer_id' "
                        . "ORDER BY o.order_number DESC";
                $result = $db->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $order_id = $row['id']
                        ?>
                        <div class="row mt-5 border-bottom">                            
                            <div class="col-lg-4">
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
                                        <li><strong>Order Date</strong>: <?= $row['order_date'] ?></li>
                                        <?php
                                        // if the status is 'advance payment failed', show a upload again option
                                        if ($row['order_status'] == '2') { // failed payment
                                            ?>
                                            <li class="text-danger"><strong>Rejected Reason</strong>: <?= $row['slip_rejected'] ?></li>
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
                                    </ul><br>
                                    <?php
                                    // total order value
                                    $sql2 = "SELECT SUM(unit_price * qty) AS OrderTotal, orders.discount_applied "
                                            . "FROM order_items "
                                            . "INNER JOIN orders ON orders.id=order_items.order_id "
                                            . "WHERE order_id='$order_id' GROUP BY order_id";
                                    $result2 = $db->query($sql2);
                                    $row2 = $result2->fetch_assoc();
                                    $OrderTotal = $row2['OrderTotal'];
                                    $discount = $row2['discount_applied'];
                                    $finalOrderTotal = $OrderTotal - ($OrderTotal * $discount);
                                    ?>
                                    <ul>
                                        <li><strong>Order Value</strong>: Rs. <?= number_format($OrderTotal, 2) ?></li>
                                        <li><strong>Discount <?= $discount * 100 ?>%</strong>: Rs. <?= number_format($OrderTotal * $discount, 2) ?></li>
                                        <li><strong>Final Order Value </strong>: Rs. <?= number_format($finalOrderTotal, 2) ?></li>                                    
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="portfolio-info">
                                    <h3>Payment Records</h3>
                                    <?php
                                    $sql3 = "SELECT * FROM order_payments p "
                                            . "INNER JOIN payment_methods pm ON pm.PayMethodId=p.PaymentMethod "
                                            . "WHERE p.OrderId='$order_id' ";
                                    $result3 = $db->query($sql3);
                                    if ($result3->num_rows > 0) {
                                        ?>
                                        <table class="table table-hover text-nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Paid Amount</th>
                                                    <th>Payment Method</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                while ($row3 = $result3->fetch_assoc()) {
                                                    ?>
                                                    <tr>
                                                        <td><?= $row3['PaymentDate'] ?></td>
                                                        <td>Rs. <?= number_format($row3['PaymentAmount'], 2) ?></td>
                                                        <td><?= $row3['PaymentMethod'] ?></td>
                                                    </tr>
                                                    <?php
                                                }
                                            } else {
                                                echo "<p>No payment records found</p>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="portfolio-info">
                                    <?php
                                    // total of payments made
                                    $sql4 = "SELECT SUM(PaymentAmount) AS TotalPaid "
                                            . "FROM order_payments p "
                                            . "WHERE OrderId='$order_id' GROUP BY p.OrderId";
                                    $result4 = $db->query($sql4);
                                    if ($result4->num_rows > 0) { // payment records found
                                        $row4 = $result4->fetch_assoc();
                                        $TotalPaid = $row4['TotalPaid'];
                                        $Due = $finalOrderTotal - $TotalPaid;
                                    } else { // no payments have made yet
                                        $TotalPaid = 0;
                                        $Due = $finalOrderTotal - $TotalPaid;
                                    }
                                    ?>
                                    <ul>
                                        <li><strong>Total Amount Paid</strong>:<br> Rs. <?= number_format($TotalPaid, 2) ?></li><br>
                                        <li class="text-danger"><strong>Due Amount</strong>: Rs. <?= number_format($Due, 2) ?></li>                                   
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
