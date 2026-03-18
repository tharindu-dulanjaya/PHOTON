<?php
ob_start();
include_once '../init.php';
include '../../mail.php';

$db = dbConn();

$link = "Create Purchase Order";
$breadcrumb_item = "Purchase Order";
$breadcrumb_item_active = "Create";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('10'); // 10 is the module id for PO Management

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    extract($_GET); // to get the item id when redirected from re-order button in inventory
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);

    // input fields are already validated for not empty before submitting the form using js

    $message = array();

    if (empty($supplier_id)) {
        $message['supplier_id'] = "Please select the supplier";
    }
    if (empty($delivery_date)) {
        $message['delivery_date'] = "Please select the expected delivery date";
    }
    // check if atleast one item and the quantity is set
    if (!isset($item_id) || !isset($qty)) {
        $message['items'] = "Please select the item & the quantity to purchase";
    } elseif (count($item_id) !== count($qty)) {
        $message['items'] = "Please select the item & the relevant quantity";
    }

    if (empty($message)) {

        // insert data in to purchase_orders table
        $po_date = date('Y-m-d');

        // get the last row id of the table
        $sql = "SELECT PO_Id FROM purchase_orders ORDER BY PO_Id DESC LIMIT 1";
        $result = $db->query($sql);
        $row = $result->fetch_assoc();
        $lastRow = $row['PO_Id'];
        $poNumber = $lastRow + 1;

        // generate a PO number
        $po_num = 'PO-' . date('y') . date('m') . date('d') . $poNumber;

        // create a token to give access to suppier to view PO details        
        $token = bin2hex(random_bytes(16));  // generates a random binary number & converts to hexa decimal

        $sql = "INSERT INTO purchase_orders(PO_Number, PO_Date, SupplierId, PaymentMethod, PO_Status, ExpectedDelivery, Token) VALUES ('$po_num','$po_date','$supplier_id','TT','1','$delivery_date','$token')";
        $db->query($sql);

        // get the primary key value of the last inserted row of the purchase_orders table
        $po_id = $db->insert_id;

        // insert data in to purchase_order_items table
        foreach ($item_id as $key => $value) { // key has 0,1,2,.. (array indexes)
            $q = $qty[$key];
            $sql = "INSERT INTO purchase_order_items(Po_id, ItemId, Quantity) VALUES ('$po_id','$value','$q')";
            $db->query($sql);
        }

        // get the supplier email to send the purchase order
        $sql = "SELECT SupplierEmail, SupplierName from suppliers WHERE SupplierId = '$supplier_id'";
        $result = $db->query($sql);
        $row = $result->fetch_assoc();

        $email = $row['SupplierEmail'];
        $name = $row['SupplierName'];

        $msg = "<h4>New Purchase Order</h4>";
        $msg .= "<p>Dear $name,<br><br>We are pleased to inform you that a new purchase order has been placed by PHOTON Technologies Pvt Ltd. <br>Please review the order details and confirm the expected delivery date & the prices.<br><br></p>";
        $msg .= "<a href='http://localhost/photon/system/purchase_orders/supplier_po_details.php?token=$token'>Click here to view the order details</a>";
        $msg .= "<br><p>Thank you for your cooperation.<br><br>Best regards,<br>PHOTON Technologies Pvt Ltd<br><br></p>";
        sendEmail($email, $name, "New Purchase Order Received", $msg);

        header("Location:manage.php");
    }
}
?>
<div class="row">
    <div class="col-12">
        <a href="manage.php" class="btn btn-outline-dark mb-2"><i class="fas fa-arrow-left">  </i> Go Back</a>
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Purchase Order</h3>
            </div>
            <div class="card-body col-10">
                <form id="createPO" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                    <div class="row mb-3">                        
                        <div class="col-md-8 form-group">
                            <label for="supplier_id">Supplier:</label>
                            <select name="supplier_id" id="supplier_id" class="form-control" required>
                                <option value="">--</option>
                                <?php
                                $sql = "SELECT SupplierId, SupplierName FROM suppliers WHERE status = '1'";
                                $result = $db->query($sql);
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        ?>
                                        <option value="<?= $row['SupplierId'] ?>" <?= @$supplier_id == $row['SupplierId'] ? 'selected' : '' ?> ><?= $row['SupplierName'] ?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                            <span class="error_span text-danger"><?= @$message['supplier_id'] ?></span>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="delivery_date">Delivery Expected On:</label>
                            <input type="date" name="delivery_date" id="delivery_date" min="<?= date('Y-m-d'); ?>" class="form-control" value="<?= @$delivery_date ?>" required>
                            <span class="error_span text-danger"><?= @$message['delivery_date'] ?></span>
                        </div>
                    </div>

                    <table class="table table-light" id="items">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th></th>                                
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="items-row">
                                <td>
                                    <select name="item_id[]" id="item_id" class="form-control" required>
                                        <option value="">--</option>
                                        <?php
                                        $sql = "SELECT id, item_name FROM items WHERE status = '1'";
                                        $result = $db->query($sql);
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                // $itemid is set only if redirected from the re-order button in inventory
                                                if (isset($_GET['itemid'])) {
                                                    ?>
                                                    <option value="<?= $row['id'] ?>" <?= ($row['id'] == $itemid) ? 'selected' : '' ?>><?= $row['item_name'] ?></option>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <option value="<?= $row['id'] ?>"><?= $row['item_name'] ?></option>
                                                    <?php
                                                }
                                            }
                                        }
                                        ?>
                                    </select>                                    
                                </td>
                                <td>
                                    <input type="number" name="qty[]" id="qty" class="form-control " min="1" required>                                    
                                </td>
                                <td>
                                    <i id="removeBtn" class="fas fa-trash" > </i>
                                </td>
                            </tr>
                        </tbody>
                        <span class="error_span text-danger"><?= @$message['items'] ?></span>
                    </table>
                    <div class="mb-5">
                        <button type="button" id="addBtn" class="btn btn-dark"><i class="fas fa-plus"></i> Add item </button>
                    </div>
                    <button type="submit" class="btn btn-info" <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>>Send Purchase Order</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include '../layouts.php';
?>

<script>

    // disable keyboard inputs in to quantity selector
    document.getElementById('qty').addEventListener('keydown', function (event) {
        event.preventDefault();
    });

    $(document).ready(function () {

        // hide the delete icon in the first row
        $('#items tbody tr:first-child #removeBtn').hide();

        function addItems() {
            var tableBody = $('#items tbody');
            var newRow = tableBody.find('.items-row').first().clone(true);

            // clear input values in the cloned row
            newRow.find('input').val('');

            // make the delete button visible for the new row
            newRow.find('#removeBtn').show();

            // append the cloned row to the table body
            tableBody.append(newRow);
        }

        function removeItems(button) {
            var row = $(button).closest('tr');
            row.remove();
        }

        // call the addItems() function when addBtn is clicked
        $('#addBtn').click(addItems);
        $('#items').on('click', '#removeBtn', function () {
            removeItems(this);
        });

        // when click on the form submit button, this functions checks if all the fields are filled
        $('#createPO').submit(function (event) {

            // to check if the form inputs are valid. initially valid = true
            var valid = true;

            // check if supplier & delivery date is not empty
            if ($('#supplier_id').val().trim() === '') {
                valid = false;
                Swal.fire({
                    icon: 'info',
                    title: 'Select the Supplier',
                    showConfirmButton: false,
                    timer: 1500
                });
                return false;
            }
            if ($('#delivery_date').val().trim() === '') {
                valid = false;
                Swal.fire({
                    icon: 'info',
                    title: 'Select the Expected delivery date',
                    showConfirmButton: false,
                    timer: 1500
                });
                return false;
            }


            // iterates over each item-row
            $('.items-row').each(function () {
                var item = $(this).find('#item_id').val();
                var quantity = $(this).find('#qty').val();

                // check if item, quantity, and unit price are not empty
                if (!item) { // check if the item field is empty
                    valid = false;
                    Swal.fire({
                        icon: 'info',
                        title: 'Select the Item Name',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    return false; // stop further execution of the code
                }
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
            });
            if (!valid) {
                event.preventDefault();  // if valid is false, prevent the form submission 
            }
        });

    });
</script>