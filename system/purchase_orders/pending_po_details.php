<?php
ob_start();
include_once '../init.php';
$db = dbConn();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    extract($_GET); // to get the po_id    

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
}

$link = "Pending PO Details";
$breadcrumb_item = "Purchase Order";
$breadcrumb_item_active = "Pending";
?> 
<div class="row">
    <div class="col-12">
        <a href="manage.php" class="btn btn-outline-dark mb-2"><i class="fas fa-arrow-left">  </i> View All</a>
        <div class="invoice p-3 mb-3" id="contentToPrint">
            <div class="row mb-3">
                <div class="col-12">
                    <h4>
                        <i class="fas fa-store"></i> Purchase Order
                        <small class="float-right">Date: <?= $po_date ?></small>
                    </h4>
                </div>
            </div>
            <div class="row invoice-info">
                <div class="col-sm-4 invoice-col">
                    Purchase Order <b># <?= $po_num ?></b><br>

                </div>
                <div class="col-sm-4 invoice-col">
                    <address>
                        <strong>Estimated Delivery on: <?= $exp_delivery ?></strong><br>
                        <p>Present Status : <span class="
                            <?php
                            if ($po_status == '1') {
                                echo 'badge badge-warning';
                            } elseif ($po_status == '2') {
                                echo 'badge badge-info';
                            } elseif ($po_status == '3') {
                                echo 'badge badge-danger';
                            } elseif ($po_status == '4') {
                                echo 'badge badge-success';
                            }
                            ?>
                                                  "> <?= $purchase_order_status ?> </span></p>

                    </address>
                </div>
                <div class="col-sm-4 invoice-col">
                    <address>
                        <strong><?= $supplier ?></strong><br>
                        <?= $sup_email ?><br>
                        <?= $sup_phone ?><br>
                        <?= $sup_address ?><br>
                        <?= $sup_country ?>
                    </address>
                </div>                
            </div>

            <!-- display purchase order items -->
            <div class="row">
                <div class="col-12 table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>                        
                                <th>Product Name</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                $sql = "SELECT * FROM purchase_order_items poi INNER JOIN items i ON i.id = poi.ItemId WHERE Po_id = '$po_id'";
                                $result = $db->query($sql);

                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?= $row['item_name'] ?></td>
                                        <td><?= $row['Quantity'] ?></td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>


            <!-- this row will not appear when printing -->
            <div class="row no-print">
                <div class="col-12">
                    <button type="button" onclick="printContent()" class="btn btn-primary float-right"><i class="fas fa-print"></i> Print</button>

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
    function printContent() {
        var content = document.getElementById("contentToPrint").innerHTML;
        var originalBody = document.body.innerHTML;
        document.body.innerHTML = content;
        window.print();
        document.body.innerHTML = originalBody;
    }
</script>