<?php
ob_start();
include_once '../init.php';
include '../../mail.php';

$db = dbConn();

$link = "Accepted Purchase Order";
$breadcrumb_item = "Purchase Order";
$breadcrumb_item_active = "Details";

extract($_GET); // to get the po_id
extract($_POST); // when payment copy is uploaded

$sql = "SELECT * FROM purchase_orders po INNER JOIN suppliers s ON s.SupplierId = po.SupplierId INNER JOIN purchase_order_status pos ON pos.PO_StatusId = po.PO_Status WHERE PO_Id = $po_id";
$result = $db->query($sql);
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'upload') {

    $message = array();

    // payment copy upload
    if (!empty($_FILES['payment_copy']['name'])) {

        $file = $_FILES['payment_copy'];
        $location = "../../uploads/po_payments";
        $uploadResult = uploadFile($file, $location);
        if ($uploadResult['upload']) {
            $payment_copy = $uploadResult['file'];
        } else {
            $error = $uploadResult['error_file'];
            $message['payment_copy'] = "<br>File upload failed : $error";
        }
    } else {
        $message['payment_copy'] = "Please upload the payment copy!";
    }

    if (empty($message)) {
        $sql = "UPDATE purchase_orders SET PaymentCopy = '$payment_copy' WHERE PO_Id = '$po_id'";
        $db->query($sql);
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'submit') {

    if (!empty($payment_copy)) {

        // update status as 'paid'
        $sql = "UPDATE purchase_orders SET PO_Status = '4' WHERE Po_id='$po_id'";
        $db->query($sql);

        // retrieve the token of the supplier
        $sql = "SELECT Token FROM purchase_orders WHERE PO_Id = '$po_id'";
        $result = $db->query($sql);
        $row = $result->fetch_assoc();
        $token = $row['Token'];

        $msg = "<h4>Payment Details Updated for #$po_num </h4>";
        $msg .= "<p>Dear $supplier,<br><br>We are pleased to inform you that we have made the payment for the Purchase Order # $po_num <br>Please review the payment details and confirm the payment.<br><br></p>";
        $msg .= "<a href='http://localhost/photon/system/purchase_orders/supplier_payment_review.php?token=$token'>Click here to view the payment details</a>";
        $msg .= "<br><p>Thank you for your cooperation.<br><br>Best regards,<br>PHOTON Technologies Pvt Ltd<br><br></p>";
        sendEmail($sup_email, $supplier, "Payment Details Updated", $msg);

        // then() method is used to redirect only after displaying the sweet alert
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: '',
                html: '<h4>Email has been sent successfully</h4>',
                showCloseButton: false,
                showConfirmButton: false,
                timer: 2500
            }).then(function() {
                window.location.href = 'manage.php';
            });
          </script>";
    }
}
?> 
<div class="row">
    <div class="col-12">
        <a href="manage.php" class="btn btn-outline-dark mb-2"><i class="fas fa-arrow-left">  </i> Go Back</a>
        <div class="invoice p-5 mb-3" id="contentToPrint">
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
                    <p>Present Status : <span class="badge badge-info"> <?= $purchase_order_status ?> </span></p>
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

                    <div class="mt-5">
                        <form id="paymentForm" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data" novalidate>

                            <h4>Please pay the amount<span class="text-danger"> $ <?= number_format($finalTotal, 2) ?></span> <br> on or before <span class="text-danger"><?= $pay_before ?></span></h4>
                            <div class="input-group mt-3">
                                <input type="file" class="form-control" id="payment_copy" name="payment_copy">
                                <input type="hidden" name='po_id' value="<?= $po_id ?>">
                                <input type="hidden" name='action' value="upload">
                                <button class="btn btn-primary" type="submit" >Upload & Preview</button>
                            </div>
                            <div class="mb-3">
                                <span class="error_span text-danger"><?= @$message['payment_copy'] ?></span>
                            </div>
                        </form>
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
                        ?>

                        <div class="text-end mt-5">
                            <form id="submit_payment" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                                <input type="hidden" name='po_id' value="<?= $po_id ?>">
                                <input type="hidden" name='action' value="submit">
                                <button type="submit" class="btn btn-warning"><i class="fas fa-paper-plane"></i> Submit Payment Details</button>  
                            </form>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include '../layouts.php';
?>
<script>

    $(document).ready(function () { // ensure the DOM is fully loaded
        // when click on upload & preview button
        $('#paymentForm').on('submit', function (event) {
            var paymentCopy = $('#payment_copy').val();
            if (paymentCopy === '') { // no file is attached
                event.preventDefault(); // prevent form submission
                Swal.fire({
                    icon: 'info',
                    title: 'Select the Payment Copy!',
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        });

    });

</script>