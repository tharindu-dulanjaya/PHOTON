<?php
ob_start();
include_once '../init.php';

$db = dbConn();

extract($_GET); // to get the $token value 
extract($_POST);

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
    } else {
        // no such token
        header("Location: http://localhost/photon/web/index.php");
    }
} else {
    // redirect if token is not set or empty
    header("Location: http://localhost/photon/web/index.php");
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // update status as 'accepted' and set total
    $sql = "UPDATE purchase_orders SET PO_Total = '$finaltotal', PO_Status = '2' WHERE Po_id='$po_id'";
    $db->query($sql);

    // then() method is used to redirect only after displaying the sweet alert
    echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Purchase Order Accepted',
                showCloseButton: false,
                showConfirmButton: false,
                timer: 2500
            }).then(function() {
                window.location.href = 'supplier_po_preview.php?token=$token';
            });
          </script>";
}

$link = "Preview Purchase Order #" . $po_num;
$breadcrumb_item = "Purchase Order";
$breadcrumb_item_active = "Preview";
?> 
<div class="row">
    <div class="col-12">
        <div class="invoice p-5 mb-3" id="contentToPrint">
            <div class="row mb-3">
                <div class="col-12 mb-3">
                    <h4>
                        <i class="fas fa-store"></i> Purchase Order
                        <small class="float-right">Date: <?= $po_date ?></small>
                    </h4>
                </div>
            </div>

            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
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
                        <p>Present Status : <span class="
                            <?php
                            if ($po_status == '1') {
                                echo 'badge badge-warning';
                            } elseif ($po_status == '2') {
                                echo 'badge badge-info';
                            } elseif ($po_status == '3') {
                                echo 'badge badge-danger';
                            } elseif ($po_status == '4') {
                                echo 'badge badge-primary';
                            } elseif ($po_status == '5') {
                                echo 'badge badge-success';
                            }
                            ?>
                                                  "> <?= $purchase_order_status ?> </span></p>

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
                                    <th>Available Quantity</th>
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
                        <p class="lead">Payment</p>

                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>Pay Before:</th>
                                        <td><?= $pay_before ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
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
                </div>
                <div class="row no-print">
                    <div class="col-12">
                        <button type="button" onclick="printContent()" class="btn btn-outline-dark"><i class="fas fa-print"></i> Print</button>                        
                        <input type="hidden" name="po_id" value="<?= $po_id ?>">
                        <input type="hidden" name="token" value="<?= $token ?>">
                        <input type="hidden" name="finaltotal" value="<?= $finalTotal ?>">
                        <!--if the PO status is other than 'pending', the button is disabled-->
                        <button type="submit" class="btn btn-primary float-right" <?= $po_status == 1 ? '' : 'disabled' ?> ><i class="fas fa-check"></i> Accept Purchase Order</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include '../supplier_layouts.php';
?>
<script>
    function printContent() {
        var content = document.getElementById("contentToPrint").innerHTML;
        var originalBody = document.body.innerHTML;
        document.body.innerHTML = content;
        window.print();
        document.body.innerHTML = originalBody;
    }
</script>
