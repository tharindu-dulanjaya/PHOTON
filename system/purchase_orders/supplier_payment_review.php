<?php
ob_start();
include_once '../init.php';

$db = dbConn();

extract($_GET);
extract($_POST);

// have to send the token in a hidden field in form submits and header redirects. otherwise will be redirected to website
if (isset($token) && !empty($token)) { // token cannot be empty
    $sql = "SELECT * FROM purchase_orders po INNER JOIN suppliers s ON s.SupplierId = po.SupplierId INNER JOIN purchase_order_status pos ON pos.PO_StatusId = po.PO_Status WHERE po.Token = '$token'";
    $result = $db->query($sql);

    if ($result->num_rows > 0) { // token exists
        $row = $result->fetch_assoc();

        $po_id = $row['PO_Id'];
        $po_num = $row['PO_Number'];
        $po_date = $row['PO_Date'];
        $supplier = $row['SupplierName'];
        $pay_method = $row['PaymentMethod'];
        $po_status = $row['PO_Status']; // status id
        $purchase_order_status = $row['PurchaseOrderStatus']; // status name
        $exp_delivery = $row['ExpectedDelivery'];
        $sup_email = $row['SupplierEmail'];
        $sup_phone = $row['SupplierPhone'];
        $sup_address = $row['Address'];
        $sup_country = $row['Country'];
        $sup_discount = $row['SupplierDiscount'];
        $pay_before = $row['PayBefore'];
        $payment_copy = $row['PaymentCopy'];
    } else {
        // no such token
        header("Location: http://localhost/photon/web/index.php");
    }
} else {
    // redirect if token is not set or empty
    header("Location: http://localhost/photon/web/index.php");
}

// payment confirmed
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'success') {
    // update status as 'confirmed'
    $sql = "UPDATE purchase_orders SET PO_Status = '5' WHERE Po_id='$po_id'";
    $db->query($sql);

    echo "<script>
            Swal.fire({
                icon: 'success',
                title: '',
                html: '<h4>Payment has been confirmed</h4>',
                showCloseButton: false,
                showConfirmButton: false,
                timer: 3000
            }).then(function() {
                window.location.href = 'supplier_payment_review.php?token=$token';
            });
          </script>";
}
// payment not received
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'failed') {

    if (!empty($reason)) {
        $reason = dataClean($reason);

        // update the reason & status as 'payment failed'
        $sql = "UPDATE purchase_orders SET PO_Status = '6', PaymentNotReceived = '$reason' WHERE Po_id='$po_id'";
        $db->query($sql);

        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: '',
                html: '<h4>Payment marked as Not Received</h4>',
                showCloseButton: false,
                showConfirmButton: false
            }).then(function() {
                window.location.href = 'supplier_payment_review.php?token=$token';
            });
          </script>";
    }
}

$link = "Review Payment #" . $po_num;
$breadcrumb_item = "Purchase Order";
$breadcrumb_item_active = "Payment Details";
?>

<!-- payment not received modal -->
<div class="modal fade" id="paymentFailed" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="createDepartLabel">Payment Not Received</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="payment_failed" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                <div class="modal-body">
                    <label for="reason" class="col-form-label">Please mention the reason for selecting this payment as not received</label>
                    <textarea type="text" class="form-control" name="reason" id="reason"></textarea>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name='token' value="<?= $token ?>">
                    <input type="hidden" name="action" value="failed">
                    <button type="submit" class="btn btn-danger">Submit as Not Received</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="invoice p-5 mb-3">
            <div class="row mb-3">
                <div class="col-12 mb-3">
                    <h4>
                        <i class="fas fa-store"></i> Purchase Order
                        <small class="float-right">Created Date: <?= $po_date ?></small>
                    </h4>
                </div>
            </div>

            <div class="row invoice-info">
                <div class="col-sm-4 invoice-col">
                    <address>
                        <strong><?= $supplier ?></strong><br>
                        <?= $sup_email ?><br>
                        <?= $sup_phone ?><br>
                        <?= $sup_address ?><br>
                        <?= $sup_country ?>
                    </address>
                </div>                    
                <div class="col-sm-4 invoice-col">
                    <address>
                        <strong>Estimated Delivery on: <span class="text-danger"><?= $exp_delivery ?></span></strong>                            
                    </address>
                </div>
                <div class="col-sm-4 invoice-col">
                    Purchase Order <b># <?= $po_num ?></b><br><br>                    
                    <p>Present Status : <span class="<?= $po_status == '5' ? 'badge badge-primary' : 'badge badge-warning' ?>"> <?= $purchase_order_status ?> </span></p>
                </div>

            </div>

            <!-- display purchase order items -->
            <div class="row">
                <div class="col-12 table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr> 
                                <th>Product Name</th>
                                <th>Requested Quantity</th>                                                                        
                                <th>Accepted Quantity</th>
                                <th>Unit Price</th>
                                <th>Sub Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                $sql = "SELECT * FROM purchase_order_items poi INNER JOIN items i ON i.id = poi.ItemId WHERE Po_id = '$po_id'";
                                $result = $db->query($sql);
                                $total = 0; // have to define as 0 initially
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>                                            
                                        <td><?= $row['item_name'] ?></td>
                                        <td><?= $row['Quantity'] ?></td>
                                        <td><?= $row['SupplierQuantity'] ?></td>
                                        <td>$ <?= number_format($row['UnitPrice'], 2) ?></td>
                                        <td>
                                            <?php
                                            $subtotal = $row['SupplierQuantity'] * $row['UnitPrice'];
                                            $total += $subtotal; // get the total
                                            echo '$ ' . number_format($subtotal, 2); // display subtotal
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
            </div>

            <div class="row mt-5">
                <div class="col-6">
                    <p class="lead">Total Amount</p>

                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th>Subtotal:</th>
                                    <td>$ <?= number_format($total, 2) ?></td>
                                </tr>
                                <tr>
                                    <th>Discount (<?= $sup_discount ?>%)</th>
                                    <td>
                                        <?php
                                        $discount = $total * ($sup_discount / 100);
                                        $finalTotal = $total - $discount;
                                        echo '$ ' . number_format($discount, 2);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Final Total:</th>
                                    <td><b>$ <?= number_format($finalTotal, 2) ?></b></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-6">
                    <?php
                    // display the uploaded file if exists
                    if (!empty($payment_copy)) {
                        echo '<h4>Uploaded Payment Copy</h4>';
                        //extract the file extension
                        $file_ext = pathinfo($payment_copy, PATHINFO_EXTENSION);
                        //check if the extension is pdf type
                        if ($file_ext == 'pdf') {
                            ?>
                            <embed src="../../uploads/po_payments/<?= $payment_copy ?>" type="application/pdf" width="100%" height="500px" />
                            <?php
                            //check if the extension is image type
                        } else if (in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
                            ?>
                            <img src="../../uploads/po_payments/<?= $payment_copy ?>" style="max-width: 100%; height: auto;" />
                            <?php
                        }
                    }
                    ?>
                    <div class=" text-end mt-5">
                        <form id="submit_payment" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                            <input type="hidden" name='po_id' value="<?= $po_id ?>">
                            <input type="hidden" name='token' value="<?= $token ?>">
                            <input type="hidden" name="action" value="success">
                            <button type="button" class="btn btn-danger" <?= $po_status == '5' ? 'disabled' : '' ?> data-bs-toggle="modal" data-bs-target="#paymentFailed"> Payment not received </button>
                            <button type="submit" class="btn btn-success" <?= $po_status == '5' ? 'disabled' : '' ?>><i class="fas fa-check"></i> Confirm 'Payment Received'</button>  
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include '../supplier_layouts.php';
?>
<script>

    $(document).ready(function () { // ensure the DOM is fully loaded

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
                event.preventDefault();  // prevent the form submission 
            }
        });
    });

</script>