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
        $sup_pay_before = $row['PayBefore'];
    } else {
        // no such token
        header("Location: http://localhost/photon/web/index.php");
    }
} else {
    // redirect if token is not set or empty
    header("Location: http://localhost/photon/web/index.php");
}

// accept purchase order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'accept') {

    // status is pending
    if ($po_status == '1') {
        // extract submitted form field data. get $po_id
        // check if all id, avail_qty and unitprices are set
        if (isset($_POST['id']) && isset($_POST['avail_qty']) && isset($_POST['unit_price'])) {
            $ids = $_POST['id'];
            $avail_qtys = $_POST['avail_qty'];
            $unit_prices = $_POST['unit_price'];

            // Check if any avail_qtys the supplier entered is larger than zero (all zero means he can't accept the PO )
            foreach ($avail_qtys as $avail_qty) {
                if ($avail_qty > 0) { // if any avail_qty is larger than zero, supplier can accept PO
                    foreach ($ids as $key => $id) { //$key is the index of the array. 0,1,2..
                        $avail_qty = $avail_qtys[$key];
                        $unit_price = $unit_prices[$key];

                        //insert each available_qty & unitprice of the item to the table
                        $sql = "UPDATE purchase_order_items SET SupplierQuantity = '$avail_qty', UnitPrice = '$unit_price' WHERE ItemId = '$id' AND Po_id = '$po_id'";
                        $db->query($sql);
                    }

                    // update discount & pay before date in the table
                    $sql = "UPDATE purchase_orders SET SupplierDiscount='$discount', PayBefore='$pay_before' WHERE Po_id='$po_id'";
                    $db->query($sql);

                    // check if the delivery_date is set (updated)
                    if (isset($_POST['delivery_date']) && !empty($_POST['delivery_date'])) {
                        $sql = "UPDATE purchase_orders SET ExpectedDelivery='$delivery_date' WHERE Po_id='$po_id'";
                        $db->query($sql);
                    }
                    // go to preview page when click on save button
                    // token is required to get data to the preview
                    header("Location: supplier_po_preview.php?token=$token");
                } else { // all the available qtys are zero. so supplier cannot accept the purchase order
                    echo "<script>
                            Swal.fire({
                                icon: 'info',
                                title: '',
                                html: '<h4>Quantity cannot be zero for all products</h4>',
                                showCloseButton: false,
                                showConfirmButton: false,
                                confirmButtonText: 'Close',
                                timer: 3000
                                });
                            </script>";
                }
            }
        }
    } else {
        // if the status is other than pending, cannot allow to make changes
        header("Location: supplier_po_preview.php?token=$token");
    }
}
// reject purchase order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'reject') {
    if (!empty($reason)) {
        $reason = dataClean($reason);

        // update the reason & status as 'rejected' and also delete the token
        $sql = "UPDATE purchase_orders SET PO_Status = '3', RejectRemarks = '$reason', Token = '' WHERE Po_id='$po_id'";
        $db->query($sql);

        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: '',
                html: '<h4>Purchase Order has been Rejected!</h4>',
                showCloseButton: false,
                showConfirmButton: false
            }).then(function() {
                window.location.href = 'http://localhost/photon/web/index.php';
            });
          </script>";
    }
}

$link = "Review Purchase Order #" . $po_num;
$breadcrumb_item = "Purchase Order";
$breadcrumb_item_active = "Details";
?> 

<!-- reject PO modal -->
<div class="modal fade" id="rejectPO">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="createDepartLabel">Reject Purchase Order</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="payment_failed" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                <div class="modal-body">
                    <label for="reason" class="col-form-label">Please mention the reason for rejecting this purchase order</label>
                    <textarea type="text" class="form-control" name="reason" id="reason"></textarea>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name='token' value="<?= $token ?>">
                    <input type="hidden" name="action" value="reject">
                    <button type="submit" class="btn btn-danger">Reject PO</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="invoice p-3 mb-3">
            <div class="row mb-3">
                <div class="col-12">
                    <h4>
                        <i class="fas fa-store"></i> Purchase Order
                        <small class="float-right">Date: <?= $po_date ?></small>
                    </h4>
                </div>
            </div>

            <form id="supplier_po" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                <div class="row invoice-info">
                    <div class="col-sm-4 invoice-col">
                        Purchase Order <b># <?= $po_num ?></b><br><br>                    
                        <p>Present Status : <span class="
                            <?php
                            if ($po_status == '1') {
                                echo 'badge badge-warning';
                            } elseif ($po_status == '2') {
                                echo 'badge badge-info';
                            } elseif ($po_status == '3') {
                                echo 'badge badge-dark';
                            } elseif ($po_status == '4') {
                                echo 'badge badge-primary';
                            } elseif ($po_status == '5') {
                                echo 'badge badge-success';
                            } elseif ($po_status == '4') {
                                echo 'badge badge-danger';
                            }
                            ?>
                                                  "> <?= $purchase_order_status ?> </span></p>

                    </div>
                    <div class="col-sm-4 invoice-col">
                        <address>
                            <strong>Estimated Delivery on: <span class="text-danger"><?= $exp_delivery ?></span></strong><br><br>
                            Change Delivery Date :
                            <?php
                                $currentDate = date('Y-m-d');
                                $minDate = date('Y-m-d', strtotime('+1 month', strtotime($currentDate)));
                            ?>
                            <input type="date" name="delivery_date" id="delivery_date" min="<?= $minDate ?>" class="form-control col-8" value="<?= $exp_delivery ?>">
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
                        <table class="table table-bordered" id="items">
                            <thead>
                                <tr> 
                                    <th>Quantity</th>
                                    <th>Product Name</th>                                    
                                    <th>Available Quantity</th>
                                    <th>Unit Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result->num_rows > 0) {
                                    $sql = "SELECT * FROM purchase_order_items poi INNER JOIN items i ON i.id = poi.ItemId WHERE Po_id = '$po_id'";
                                    $result = $db->query($sql);

                                    while ($row = $result->fetch_assoc()) {
                                        ?>
                                        <tr class="items-row">
                                            <td><?= $row['Quantity'] ?></td>
                                            <td><?= $row['item_name'] ?></td>
                                            <td>
                                                <!-- keyboard inputs are disabled & only selector values are accepted. applied using avail_qty class-->
                                                <input type="number" id="avail_qty" name="avail_qty[]" class="avail_qty form-control col-6" max="<?= $row['Quantity'] ?>" min="0" onfocus="this.removeAttribute('readonly');" onblur="this.setAttribute('readonly', true);" value="<?= $row['SupplierQuantity'] ?>">
                                            </td>
                                            <td>
                                                <!--hidden field to store ids of the each item in order to insert in to purchase_order_items-->
                                                <input type="hidden" name="id[]" value="<?= $row['ItemId'] ?>"> 

                                                <div class="input-group col-10">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" id="unit_price" name="unit_price[]" min="0" class="form-control col-6" value="<?= $row['UnitPrice'] ?>">
                                                    <span class="input-group-text">.00</span>
                                                </div>
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
                    <div class="col-5">
                        <p class="lead">Supplier Terms </p>

                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>Discount (optional): </th>
                                        <td>
                                            <!-- keyboard inputs are disabled & only selector values are accepted-->                                                
                                            <div class="input-group col-6">
                                                <input type="number" id="discount" name="discount" class="form-control col-7" max="50" min="0" onfocus="this.removeAttribute('readonly');" onblur="this.setAttribute('readonly', true);" value="<?= $sup_discount ?>">
                                                <span class="input-group-text"> %</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Pay before :</th>
                                        <td>
                                            <div class="input-group col-10">
                                                <input type="date" name="pay_before" id="pay_before" min="<?= date("Y-m-d", strtotime("+1 week")); ?>" class="form-control" value="<?= @$sup_pay_before ?>">
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-7">

                    </div>
                </div>
                <div class="row">
                    <div class="col-12 text-end">
                        <!--two buttons are only enabled for 'pending' status-->
                        <button type="button" class="btn btn-danger" <?= $po_status == '1'?'':'disabled' ?> data-bs-toggle="modal" data-bs-target="#rejectPO"> Reject PO </button>
                        <!-- send token & purchase order_id in hidden fields-->
                        <input type="hidden" name="token" value="<?= $token ?>">
                        <input type="hidden" name="po_id" value="<?= $po_id ?>">
                        <input type="hidden" name="action" value="accept">                        
                        <button type="submit" class="btn btn-primary" <?= $po_status == '1'?'':'disabled' ?>><i class="fas fa-paper-plane"></i> Save & Preview</button>
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

    $(document).ready(function () {
        
        // disable keyboard inputs in to quantity selector
        $('.avail_qty').on('keydown', function (event) {
            event.preventDefault();
        });
        
        // disable keyboard inputs in to discount selector
        $('#discount').on('keydown', function (event) {
            event.preventDefault();
        });

        // reject PO modal
        $('#rejectPO').submit(function (event) {
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

        // when click on the form submit button
        $('#supplier_po').submit(function (event) {

            // to check if the form inputs are valid. initially valid = true
            var valid = true;

            // iterates over each item-row
            $('.items-row').each(function () {
                var quantity = $(this).find('#avail_qty').val();
                var unitPrice = $(this).find('#unit_price').val();

                // check if quantity, and unit price are empty
                if (!quantity) {
                    valid = false;
                    Swal.fire({
                        icon: 'info',
                        title: 'Select the Quantity',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    return false;
                }
                if (!unitPrice) {
                    valid = false;
                    Swal.fire({
                        icon: 'info',
                        title: 'Insert the Unit price',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    return false;
                }
            });

            // check if paybefore date is empty
            if ($('#pay_before').val().trim() === '') {
                valid = false;
                Swal.fire({
                    icon: 'info',
                    title: 'Select the Pay Before date',
                    showConfirmButton: false,
                    timer: 1500
                });
                return false;
            }
            if (!valid) {
                event.preventDefault();  // if valid is false, prevent the form submission 
            }
        });
    });

</script>
