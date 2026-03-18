<?php
ob_start();
session_start();
include_once '../init.php';

extract($_GET); // to get the order_id coming from the url
extract($_POST);

// success message displayed when redirected from issue.php file
if (isset($_GET['status']) && $_GET['status'] == 'success') {
    echo "<script>
            Swal.fire({
                icon: 'success',
                title: '',
                html: '<h4>Items have been issued</h4>',
                showCloseButton: false,
                showConfirmButton: false,
                timer: 3500
            });
          </script>";
}
// success message displayed when redirected from invoice.php file
if (isset($_SESSION['invoice']) && $_SESSION['invoice'] == 'success') {
    echo "<script>
            Swal.fire({
                icon: 'success',
                title: '',
                html: '<h4>Payment invoice has been emailed to the customer</h4>',
                showCloseButton: false,
                showConfirmButton: false,
                timer: 4000
            });
          </script>";
    unset($_SESSION['invoice']); // unset session after displaying the alert
}

$db = dbConn();
$sql = "SELECT o.id, "
        . "o.order_date, "
        . "t.Title, "
        . "u.FirstName, "
        . "u.LastName, "
        . "c.MillName, "
        . "c.RegNo, "
        . "u.AddressLine1, "
        . "u.AddressLine2, "
        . "u.Email, "
        . "u.MobileNo, "
        . "u.City, "
        . "d.Name, "
        . "o.delivery_name, "
        . "o.delivery_email, "
        . "o.delivery_phone, "
        . "o.delivery_address1, "
        . "o.delivery_address2, "
        . "o.delivery_city, "
        . "o.delivery_district, "
        . "o.order_number, "
        . "o.order_notes, "
        . "o.order_status, "
        . "s.OrderStatus, "
        . "o.payment_method, "
        . "o.payment_slip, "
        . "o.discount_applied, "
        . "pm.PaymentMethod "
        . "FROM orders o "
        . "INNER JOIN customers c ON o.customer_id = c.CustomerId "
        . "INNER JOIN users u ON c.UserId = u.UserId "
        . "INNER JOIN titles t ON u.TitleId = t.Id "
        . "INNER JOIN districts d ON d.Id = u.DistrictId "
        . "LEFT JOIN order_status s ON s.StatusId = o.order_status "
        . "LEFT JOIN payment_methods pm ON pm.PayMethodId = o.payment_method "
        . "WHERE o.id = '$order_id' "
        . "ORDER BY o.order_date DESC";
$result = $db->query($sql);
$row = $result->fetch_assoc();

$order_no = $row['order_number'];
$order_status = $row['order_status'];
$payment_method = $row['payment_method'];
$payment_slip = $row['payment_slip'];
$discount_applied = $row['discount_applied'];

$link = "Order Details #" . $order_no;
$breadcrumb_item = "Order";
$breadcrumb_item_active = "View Items";

// advance payment approved
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'success') {
    // update order status as 'advance payment received'
    $sql = "UPDATE orders SET order_status = '3', slip_rejected = '' WHERE id='$order_id'";
    $db->query($sql);

    echo "<script>
            Swal.fire({
                icon: 'success',
                title: '',
                html: '<h4>Payment has been confirmed</h4>',
                showCloseButton: false,
                showConfirmButton: false,
                timer: 4000
            }).then(function() {
                window.location.href = 'view_order_items.php?order_id=$order_id';
            });
          </script>";
}
// payment not received
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'failed') {

    if (!empty($reason)) {
        $reason = dataClean($reason);

        // update the reason & status as 'payment failed'
        $sql = "UPDATE orders SET order_status = '2', slip_rejected = '$reason' WHERE id='$order_id'";
        $db->query($sql);

        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: '',
                html: '<h4>Payment marked as Not Received</h4>',
                showCloseButton: false,
                showConfirmButton: false
            }).then(function() {
                window.location.href = 'view_order_items.php?order_id=$order_id';
            });
          </script>";
    }
}

// submit payment details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'submit_payment') {

    $payMethod = $_POST['payMethod'];
    $sql = "INSERT INTO order_payments(OrderId, OrderNumber, PaymentDate, PaymentAmount, PaymentMethod, Remarks) "
            . "VALUES ('$order_id','$order_no','$payment_date','$payment_amount','$payMethod','$pay_remarks')";
    $result = $db->query($sql);
    if ($result) {
        // after inserting payment details in to database, send invoice to customer by email
        echo "<script>
            Swal.fire({
                icon: 'info',
                title: '',
                html: '<h4>Payment details updated!</h4>',
                showCloseButton: false,
                showConfirmButton: false,
                timer: 2000
            }).then(function() {
                window.location.href = 'http://localhost/photon/system/payments/invoice.php?order_id=$order_id';
            });
          </script>";
        //        header("Location:http://localhost/photon/system/payments/invoice.php?order_id=$order_id");
    }
}

// mark total payment as complete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'mark_as_complete') {

    // update order status as completed
    $sql = "UPDATE orders SET order_status='9' WHERE id='$order_id'";
    $result = $db->query($sql);
    if ($result) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: '',
                html: '<h4>Order is completed!</h4>',
                showCloseButton: false,
                showConfirmButton: false,
                timer: 2000
            }).then(function() {
                window.location.href = 'view_order_items.php?order_id=$order_id';
            });
          </script>";
    }
}
?> 
<a href="<?= SYS_URL ?>orders/manage.php" class="btn btn-outline-dark mb-2"><i class="fas fa-arrow-left "> </i> Back</a>

<!-- payment not received modal -->
<div class="modal fade" id="paymentFailed">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title fs-5">Payment Not Received</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="payment_failed" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                <div class="modal-body">
                    <label for="reason" class="col-form-label">Mention the reason for selecting this payment as not received</label>
                    <textarea type="text" class="form-control" name="reason" id="reason"></textarea>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name='order_id' value="<?= $order_id ?>">
                    <input type="hidden" name="action" value="failed">
                    <button type="submit" class="btn btn-danger">Submit as Not Received</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
if ($row['payment_method'] == 2) { // bank transfer -> different layout to approve payment slip
    ?>
    <div class="row">
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title text-primary">Customer Details</h3>
                </div>
                <div class="card-body">
                    <?= $row['Title'] ?> <?= $row['FirstName'] ?> <?= $row['LastName'] ?> <br>
                    <?= $row['MillName'] ?><br>
                    <?= $row['MobileNo'] ?><br>
                    <?= $row['City'] ?><br>
                </div>
                <div class="card-header">
                    <h3 class="card-title text-primary">Order Details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            Current Order Status :
                        </div>
                        <div class="col-6">
                            <?php
                            if ($row['order_status'] == 1) {
                                echo "<span class='badge badge-warning' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            } elseif ($row['order_status'] == 2) {
                                echo "<span class='badge badge-danger' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            } elseif ($row['order_status'] == 3) {
                                echo "<span class='badge badge-info' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            } elseif ($row['order_status'] == 4) {
                                echo "<span class='badge badge-primary' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            } elseif ($row['order_status'] == 5) {
                                echo "<span class='badge badge-dark' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            } elseif ($row['order_status'] == 6) {
                                echo "<span class='badge badge-success' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            } elseif ($row['order_status'] == 7) { // cancelled
                                echo "<span class='badge badge-danger' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            } elseif ($row['order_status'] == 8) { // returned
                                echo "<span class='badge badge-danger' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            } else { // completed
                                echo "<span class='badge badge-success' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            }
                            ?>
                        </div>
                    </div><br>
                    Order Number # <b><?= $order_no ?></b><br>
                    Order Date : <b><?= $row['order_date'] ?></b><br>
                    Payment Method : <b><?= $row['PaymentMethod'] ?></b><br>
                </div>
                <div class="card-header">
                    <h3 class="card-title text-primary">Delivery Details</h3>
                </div>
                <div class="card-body">
                    <?= $row['delivery_name'] ?><br>
                    <?= $row['delivery_phone'] ?><br>
                    <?= $row['delivery_address1'] ?>, <?= $row['delivery_address2'] ?><br> 
                    <?= $row['delivery_city'] ?><br>
                    <?= $row['Name'] ?><br>
                    <p class="text-danger">Customer Notes : <?= $row['order_notes'] ?></p>
                </div>
            </div> 
        </div>
        <div class="col-8">
            <div class="card card-dark">
                <div class="card-header">
                    <h3 class="card-title">Review Payment Slip</h3>
                </div>
                <div class="card-body pt-0 pl-0 pr-0 ">
                    <?php
                    // display the uploaded file if exists
                    if (!empty($payment_slip)) {
                        //extract the file extension
                        $file_ext = pathinfo($payment_slip, PATHINFO_EXTENSION);
                        //check if the extension is pdf type
                        if ($file_ext == 'pdf') {
                            ?>
                            <embed src="../../uploads/payments/<?= $payment_slip ?>" type="application/pdf" width="100%" height="550px" />
                            <?php
                            //check if the extension is image type
                        } else if (in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
                            ?>
                            <img src="../../uploads/payments/<?= $payment_slip ?>" style="max-width: 100%; height: auto;" />
                            <?php
                        }
                    }
                    ?>
                    <div class="float-right mt-3 mr-4">
                        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                            <input type="hidden" name='order_id' value="<?= $order_id ?>">
                            <input type="hidden" name="action" value="success">

                            <?php
                            if ($order_status == '1' || $order_status == '2') { //1 = not reviewed, 2 = payment failed
                                ?>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#paymentFailed"> Payment not received </button>
                                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Approve Payment</button>
                                <?php
                            }
                            ?>
                        </form>
                    </div>
                </div>
            </div>    
        </div>
    </div>
    <?php
} else { // means COD
    ?>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">Customer Details</h3>
                </div>
                <div class="card-body">
                    Registration # <b><?= $row['RegNo'] ?></b><br><br>
                    <?= $row['Title'] ?> <?= $row['FirstName'] ?> <?= $row['LastName'] ?> <br>
                    <?= $row['MillName'] ?><br>
                    <?= $row['AddressLine1'] ?>, <?= $row['AddressLine2'] ?><br>
                    <?= $row['City'] ?><br>
                </div>
            </div>        
        </div>
        <div class="col">
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">Order Details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            Current Order Status :
                        </div>
                        <div class="col-6">
                            <?php
                            if ($row['order_status'] == 1) {
                                echo "<span class='badge badge-warning' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            } elseif ($row['order_status'] == 2) {
                                echo "<span class='badge badge-danger' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            } elseif ($row['order_status'] == 3) {
                                echo "<span class='badge badge-info' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            } elseif ($row['order_status'] == 4) {
                                echo "<span class='badge badge-primary' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            } elseif ($row['order_status'] == 5) {
                                echo "<span class='badge badge-dark' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            } elseif ($row['order_status'] == 6) {
                                echo "<span class='badge badge-success' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            } elseif ($row['order_status'] == 7) { // cancelled
                                echo "<span class='badge badge-danger' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            } elseif ($row['order_status'] == 8) { // returned
                                echo "<span class='badge badge-danger' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            } else { // completed
                                echo "<span class='badge badge-success' style='width:80%'>" . $row['OrderStatus'] . "</span>";
                            }
                            ?>
                        </div>
                    </div><br>
                    Order Number # <b><?= $order_no ?></b><br>
                    Order Date : <b><?= $row['order_date'] ?></b><br>
                    Payment Method : <b><?= $row['PaymentMethod'] ?></b><br>
                </div>
            </div>    
        </div>
        <div class="col">
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">Delivery Details</h3>
                </div>
                <div class="card-body">
                    <?= $row['delivery_name'] ?><br>
                    <?= $row['delivery_phone'] ?> / <?= $row['MobileNo'] ?><br>
                    <?= $row['delivery_address1'] ?>, <?= $row['delivery_address2'] ?><br> 
                    <?= $row['delivery_city'] ?><br>
                    <?= $row['Name'] ?><br>
                    <p style="color:red;">Customer Notes : <?= $row['order_notes'] ?></p>
                </div>
            </div>    
        </div>
    </div>
    <?php
}
?>
<div class="row">
    <div class="col-6">
        <div class="card">            
            <div class="card-header">
                <h3 class="card-title">Payment Details</h3>
            </div>
            <form id='paymentForm' action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" novalidate>
                <div class="card-body">
                    <div class="row mb-3">
                        <label for="order_no" class="col-4 col-form-label">Order Number : </label>
                        <div class="col-8">
                            <input type="text" readonly class="form-control-plaintext" name="order_no" id="order_no" value="<?= @$order_no ?>" >
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="payment_date"class="col-4 col-form-label">Payment Date:</label>
                        <div class="col-8">
                            <input type="date" name="payment_date" id="payment_date" class="form-control" max="<?= date('Y-m-d'); ?>" value="<?= @$payment_date ?>" required>
                        </div>
                        <span class="error_span text-danger mt-4"><?= @$message['payment_date'] ?></span>

                    </div>
                    <div class="row mb-3">
                        <label for="payment_amount" class="col-4 col-form-label">Payment Amount (Rs.) </label>
                        <div class="col-8">
                            <input type="text" class="form-control border border-1 border-dark-subtle" name="payment_amount" id="payment_amount" value="<?= @$payment_amount ?>" >
                        </div>
                    </div>                    
                    <div class="row mb-3">
                        <?php
                        $sql2 = "SELECT * FROM payment_methods WHERE PayMethodStatus='1'";
                        $result2 = $db->query($sql2);
                        ?>
                        <label for="payMethod" class="col-4 col-form-label">Payment Method</label>
                        <div class="col-8">
                            <select name="payMethod" id="payMethod" class="form-control border border-1 border-dark-subtle">
                                <option value="">Select Payment method</option>
                                <?php
                                while ($row2 = $result2->fetch_assoc()) {
                                    ?>
                                    <option value="<?= $row2['PayMethodId'] ?>"> <?= $row2['PaymentMethod'] ?> </option>
                                    <?php
                                }
                                ?>
                            </select>                                    
                            <span class="error_span text-danger"><?= @$message['payment_method'] ?></span>
                        </div>
                    </div>                    
                    <div class="row mt-3">
                        <div class="form-group col-12">
                            <label for="pay_remarks">Payment Remarks</label>
                            <textarea type="text" name="pay_remarks" id="pay_remarks" class="form-control"><?= @$pay_remarks ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <input type="hidden" name='order_id' value="<?= $order_id ?>">
                    <input type="hidden" name="action" value="submit_payment">
                    <?php
                    // order is cancelled or returned or completed. so submit payment button is disabled
                    if ($order_status >= 7) {
                        ?>
                        <input type="submit" value="Submit Payment" class="btn btn-success" disabled>
                        <?php
                    } else {
                        ?>
                        <input type="submit" value="Submit Payment" class="btn btn-info">
                        <?php
                    }
                    ?>
                </div>
            </form>
        </div>
    </div>
    <div class="col-6">
        <div class="card">            
            <div class="card-header bg-dark">
                <h3 class="card-title">Payment History</h3>
            </div>
            <div class="card-body table-responsive">
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
                <?php
                // total order value
                $sql4 = "SELECT SUM(unit_price * qty) AS OrderTotal "
                        . "FROM order_items "
                        . "WHERE order_id='$order_id' GROUP BY order_id";
                $result4 = $db->query($sql4);
                if ($result4->num_rows > 0) {
                    $row4 = $result4->fetch_assoc();
                    $OrderTotal = $row4['OrderTotal'];
                    $OrderDiscount = $OrderTotal * $discount_applied;
                    $finalOrderTotal = $OrderTotal - $OrderDiscount;
                    ?>
                    <div class="row ml-2 mt-3 mb-1">
                        <p> Order Total : Rs. <?= number_format($OrderTotal, 2) ?></p>
                    </div>
                    <div class="row ml-2 mb-1 text-primary">
                        <p> Discount <?= $discount_applied * 100 ?>% : Rs. -<?= number_format($OrderDiscount, 2) ?></p>
                    </div>
                    <div class="row ml-2 mb-1">
                        <p><b> Final Order Total : Rs. <?= number_format($finalOrderTotal, 2) ?></b></p>
                    </div>
                    <?php
                }
                // total of payments made
                $sql5 = "SELECT SUM(PaymentAmount) AS TotalPaid "
                        . "FROM order_payments p "
                        . "WHERE OrderId='$order_id' GROUP BY p.OrderId";
                $result5 = $db->query($sql5);
                if ($result5->num_rows > 0) { // payment records found
                    $row5 = $result5->fetch_assoc();
                    $TotalPaid = $row5['TotalPaid'];
                    $Due = $finalOrderTotal - $TotalPaid;
                } else { // no payments have made yet
                    $TotalPaid = 0;
                    $Due = $finalOrderTotal - $TotalPaid;
                }
                ?>
                <div class="row ml-2 mb-1">
                    <p> Total Amount Paid : Rs. <?= number_format($TotalPaid, 2) ?></p>
                </div>
                <div class="row ml-2 mb-1">
                    <p class="text-danger"> Due Amount : Rs. <?= number_format($Due, 2) ?></p>
                </div>
            </div>
            <div class="card-footer text-right">
                <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                    <input type="hidden" name='order_id' value="<?= $order_id ?>">
                    <input type="hidden" name="action" value="mark_as_complete">
                    <?php
                    // order is cancelled or returned or completed(7,8,9). so button is disabled
                    if ($order_status >= 7) {
                        ?>
                        <input type="submit" value="Mark as complete" class="btn btn-success" disabled>
                        <?php
                    } else {
                        ?>
                        <!--if due payment is not 0, button is disabled-->
                        <input type="submit" value="Mark as complete" class="btn btn-success" <?= @$Due <= 0 ? '' : 'disabled' ?>>
                        <?php
                    }
                    ?>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">            
            <div class="card-header bg-dark">
                <h3 class="card-title">Order Item Details</h3>
            </div>
            <!--form data is submitted to the issue.php file in inventory module-->            
            <form id="issueForm" action="../inventory/issue.php" method="post">
                <div class="card-body table-responsive p-0">
                    <?php
                    //Advanced SUB QUERY
                    $sql6 = "SELECT 
                            o.order_id,
                            o.item_id,
                            o.unit_price,
                            o.qty,
                            o.issued_qty,
                            i.item_name,
                            (COALESCE(stock_totals.total_qty, 0) - COALESCE(stock_totals.total_issued_qty, 0)) AS balance_qty
                        FROM 
                            order_items o 
                        INNER JOIN 
                            items i ON i.id = o.item_id 
                        LEFT JOIN 
                            (
                                SELECT 
                                    item_id,
                                    unit_price,
                                    SUM(qty) AS total_qty, 
                                    SUM(issued_qty) AS total_issued_qty 
                                FROM 
                                    item_stock 
                                GROUP BY 
                                    item_id,unit_price
                            ) AS stock_totals 
                            ON stock_totals.item_id = o.item_id and stock_totals.unit_price = o.unit_price 
                        WHERE 
                            o.order_id = '$order_id' 
                        GROUP BY 
                            o.order_id, o.item_id, o.unit_price";
                    $result6 = $db->query($sql6);
                    ?>
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Remaining Qty</th>
                                <th>Unit Price</th>
                                <th>Ordered Qty</th>                                
                                <th>Issued Qty</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result6->num_rows > 0) {
                                while ($row6 = $result6->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?= $row6['item_name'] ?></td>
                                        <td><?= $row6['balance_qty'] ?></td>
                                        <td><?= $row6['unit_price'] ?></td>
                                        <td><?= $row6['qty'] ?></td>                                   
                                        <td>
                                            <?php
                                            // display return button after issued (4)
                                            if ($order_status >= '4') {
                                                echo $row6['issued_qty'];

                                                if ($order_status != 7 && $order_status != 8) { // if order is not cancelld or returned by customer
                                                    ?>
                                                <td>
                                                    <!--send the order_id and item_id with the link-->
                                                    <a href="<?= SYS_URL ?>orders/return_item.php?item_id=<?= $row6['item_id'] ?>&order_id=<?= $row6['order_id'] ?>" class="btn btn-sm btn-warning mb-2"><i class="fas fa-arrow-left "> </i> Return Item</a>
                                                </td> 
                                                <?php
                                            }
                                        } else { // order is not issued yet
                                            ?>
                                    <input type="hidden" name="items[]" value="<?= $row6['item_id'] ?>">
                                    <input type="hidden" name="order_id" value="<?= $row6['order_id'] ?>">
                                    <input type="hidden" name="prices[]" value="<?= $row6['unit_price'] ?>">

                                    <!--if there is more balance qty than ordered qty, max to issue should be ordered qty
                                         balance qty is less than the ordered qty, max to issue should be balance qty -->
                                    <input type="number" id="issued_qty" name="issued_qty[]" min="1" max="<?= ($row6['balance_qty'] >= $row6['qty']) ? $row6['qty'] : $row6['balance_qty'] ?>">
                                    <?php
                                }
                                ?>
                                </td>
                                </tr>

                                <?php
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-right">
                    <?php
                    // order status below 4 means not issued yet. then show the issue button 
                    if ($order_status < 4) { // (4 = issued)
                        // if it's a bank transfer & order status 3 means payment approved. otherwise issue button is disabled
                        if ($payment_method == 2 && $order_status < 3) {
                            ?>
                            <button type="submit" class="btn btn-primary" disabled>Issue Items</button>
                            <?php
                        } else {
                            ?>
                            <button type="submit" class="btn btn-primary">Issue Items</button>
                            <?php
                        }
                    }
                    ?>

                </div>
            </form>
        </div>
    </div>
</div>

<?php
if ($order_status >= 4) { // display below section only after issueing
    ?>
    <div class="row">
        <div class="col-12">
            <div class="card">            
                <div class="card-header">
                    <h3 class="card-title">Issued Item Details</h3>
                </div>
                <div class="card-body table-responsive">
                    <?php
                    $sql7 = "SELECT * FROM orders o "
                        . "INNER JOIN order_items_issue oii ON oii.order_id=o.id "
                        . "INNER JOIN issued_serial_numbers isn ON isn.Order_Items_Issue_Id=oii.id "
                        . "INNER JOIN items i ON i.id=oii.item_id "
                        . "WHERE o.id='$order_id'";
                    $result7 = $db->query($sql7);
                    if ($result7->num_rows > 0) {
                        ?>
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Name</th>
                                    <th>Model No.</th>
                                    <th>Issued Date</th>
                                    <th>Serial Number</th>
                                    <th>QR</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($row7 = $result7->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><img src="../../uploads/products/<?= $row7['item_image'] ?>" width="150px"/></td>
                                        <td><?= $row7['item_name'] ?></td>
                                        <td><?= $row7['model_no'] ?></td>
                                        <td><?= $row7['issue_date'] ?></td>
                                        <td><?= $row7['SerialNo'] ?></td>
                                        <td><img src="../../qr/<?= $row7['QR_Image'] ?>" width="150px"/></td>
                                    </tr>
                                        <?php
                                    }
                                } else {
                                    echo 'No records found!';
                                }
                                ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>


<?php
$content = ob_get_clean();
include '../layouts.php';
?>

<script>

    $(document).ready(function () {

        // payment not received modal
        $('#payment_failed').submit(function (event) {
            // check if the reason is empty
            if ($('#reason').val().trim() === '') {
                Swal.fire({
                    icon: 'info',
                    title: '',
                    html: '<h4>Please mention the reason!</h4>',
                    showConfirmButton: false,
                    showCloseButton: false
                });
                event.preventDefault();  // prevent the form submission if reason is empty
            }
        });

        $('#paymentForm').submit(function (event) {
            var isValid = true;

            if ($('#payment_date').val().trim() === '') {
                Swal.fire({
                    icon: 'info',
                    title: '',
                    html: '<h4>Payment date is required!</h4>',
                    showConfirmButton: false,
                    timer: 1500
                });
                isValid = false;
                return false;
            }

            if ($('#payment_amount').val().trim() === '') {
                Swal.fire({
                    icon: 'info',
                    title: '',
                    html: '<h4>Payment amount is required!</h4>',
                    showConfirmButton: false,
                    timer: 1500
                });
                isValid = false;
                return false; // stop further execution for this iteration
            }

            if ($('#payment_method').val().trim() === '') {
                Swal.fire({
                    icon: 'info',
                    title: '',
                    html: '<h4>Payment method is required!</h4>',
                    showConfirmButton: false,
                    timer: 1500
                });
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault();  // prevent the form submission if validation fails
            }
        });
        
        $('#issueForm').submit(function (event) {
            var isValid = true;

            if ($('#issued_qty').val().trim() === '') {
                Swal.fire({
                    icon: 'info',
                    title: '',
                    html: '<h4>Enter issue amount!</h4>',
                    showConfirmButton: false,
                    timer: 1500
                });
                isValid = false;
                return false; // stop further execution for this iteration
            }

            if (!isValid) {
                event.preventDefault();  // prevent the form submission if validation fails
            }
        });

    });

</script>