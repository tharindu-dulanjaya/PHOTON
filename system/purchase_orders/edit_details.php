<?php
ob_start();
include_once '../init.php';
include '../../mail.php';

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('10'); // 10 is the module id for PO Management

$db = dbConn();
extract($_GET); // to get $po_id
extract($_POST);

$link = "Update Purchase Order";
$breadcrumb_item = "Purchase Order";
$breadcrumb_item_active = "Edit";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $sql = "SELECT SupplierName, ExpectedDelivery FROM purchase_orders "
            . "INNER JOIN suppliers ON suppliers.SupplierId=purchase_orders.SupplierId WHERE PO_Id = $po_id";
    $result = $db->query($sql);
    $row = $result->fetch_assoc();

    $name = $row['SupplierName'];
    $delivery_date = $row['ExpectedDelivery'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //no need to data clean since  no typing fields
    $message = array();
    if (empty($delivery_date)) {
        $message['delivery_date'] = "Please select the expected delivery date";
    }
    if (empty($item_id)) {
        $message['item_id'] = "Please select the items to purchase";
    }
    if (empty($qty)) {
        $message['qty'] = "The Quantity can not be empty!";
    }
    if (empty($message)) {
        // update data in to purchase_orders table
        $po_date = date('Y-m-d');
        $sql = "UPDATE purchase_orders SET PO_Date = '$po_date', ExpectedDelivery = '$delivery_date' WHERE PO_Id = '$po_id'";
        $db->query($sql);

        // update data in to purchase_order_items table
        $ids = $_POST['item_id']; // latest item_id (if the item is not changed this equals to initial item id)
        $qtys = $_POST['qty'];

        $prv_ids = $_POST['prv_itemid']; // initial/old item_id sent from hidden field

        foreach ($ids as $key => $id) { // key has 0,1,2,.. (array indexes)
            $q = $qtys[$key];
            $prv_itm = $prv_ids[$key];

            $sql = "UPDATE purchase_order_items SET ItemId = '$id', Quantity = '$q' WHERE Po_id = '$po_id' AND ItemId = '$prv_itm'";
            $db->query($sql);
        }

        $sql = "SELECT Token, SupplierName, SupplierEmail from purchase_orders "
                . "INNER JOIN suppliers ON suppliers.SupplierId=purchase_orders.SupplierId "
                . "WHERE purchase_orders.PO_Id = '$po_id'";
        $result = $db->query($sql);
        $row = $result->fetch_assoc();
        $token = $row['Token'];
        $name = $row['SupplierName'];
        $email = $row['SupplierEmail'];
        $subject = "Updated Purchase Order Received";

        $msg = "<h4>Updated Purchase Order</h4>";
        $msg .= "<p>Dear $name,<br><br>We would like to inform you that an updated version of the previous purchase order has been issued by our company.<br>Please review the updated order details and confirm the expected delivery date & the prices.<br><br></p>";
        $msg .= "<a href='http://localhost/photon/system/purchase_orders/supplier_po_details.php?token=$token'>Click here to view the order details</a>";
        $msg .= "<br><p>Thank you for your cooperation.<br><br>Best regards,<br>PHOTON Technologies Pvt Ltd<br><br></p>";

        sendEmail($email, $name, $subject, $msg);
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
                <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                    <div class="row mb-3">                        
                        <div class="col-md-8 form-group">
                            <label for="supplier_id">Supplier:</label>
                            <input type="text" class="form-control" value="<?= $name ?>" readonly>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="delivery_date">Delivery Expected On:</label>
                            <input type="date" name="delivery_date" id="delivery_date" class="form-control" value="<?= $delivery_date ?>" required>
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
                            <?php
                            $sql = "SELECT ItemId, Quantity FROM purchase_order_items WHERE Po_id = $po_id";
                            $result = $db->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                $prv_itemid = $row['ItemId']; //initial item id
                                ?>
                                <tr class="items-row">
                                    <td>
                                        <!--send the initial item_id in a hidden field to update the new item_id instead of it-->
                                        <input type="hidden" name="prv_itemid[]" value="<?= $prv_itemid ?>">
                                        <select name="item_id[]" id="item_id" class="form-control" required>
                                            <option value="">--</option>
                                            <?php
                                            $sql2 = "SELECT id, item_name FROM items WHERE status = 1";
                                            $result2 = $db->query($sql2);
                                            if ($result2->num_rows > 0) {
                                                while ($row2 = $result2->fetch_assoc()) {
                                                    ?>
                                                    <option value="<?= $row2['id'] ?>" <?= $row2['id'] == $prv_itemid ? 'selected' : '' ?> "><?= $row2['item_name'] ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="qty[]" id="qty" class="form-control col-6" value="<?= $row['Quantity'] ?>" min="1" required>
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>

                        </tbody>
                    </table>
                    <input type="hidden" name="po_id" value="<?= $po_id ?>">
                    <button type="submit" class="btn btn-warning float-right mt-5" <?= $privilege['Edit'] == '0' ? 'disabled' : '' ?>>Resend Purchase Order</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include '../layouts.php';
?>
