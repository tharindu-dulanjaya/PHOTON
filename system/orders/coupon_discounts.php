<?php
ob_start();
include_once '../init.php';

$link = "Coupon Discounts";
$breadcrumb_item = "Coupans";
$breadcrumb_item_active = "Manage";

// check the if the logged in user has access to this module & send CRUD privileges
$privilege = checkprivilege('3');

$db = dbConn();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);
    // create new Discount
    if ($action == 'add') {
        $new_discount = dataClean($new_discount);
        $order_count = dataClean($order_count);
        $message = array();
        if (empty($new_discount)) {
            $message['new_discount'] = "Enter new discount value!";
        }
        if (empty($order_count)) {
            $message['order_count'] = "Enter minimum order count!";
        }else{
            // check if this order count already exists
            $sql = "SELECT * FROM coupon_discount WHERE OrderCount='$order_count'";
            $result = $db->query($sql);
            if($result->num_rows>0){ // means already exists
                $message['order_count'] = "Order count already exists. Consider changing discount!";
            }
        }
        if (empty($message)) {
            $new_discount = $new_discount / 100;
            $sql = "INSERT INTO coupon_discount(OrderCount,NextOrderDiscount) VALUES ('$order_count','$new_discount')";
            $result = $db->query($sql);
            if ($result) {
                echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: '',
                            html: '<h4>Discount Added!</h4>',
                            showCloseButton: false,
                            showConfirmButton: false,
                            timer: 2000
                        });
                      </script>";
            }
        }
    }

    // edit existing discount
    if ($action == 'edit') {
        $edit_order_count = dataClean($edit_order_count);
        $edit_discount = dataClean($edit_discount);
        $message = array();
        if ($edit_order_count != 0) { // order count can be zero
            if (empty($edit_order_count)) {
                $message['edit_order_count'] = "Enter minimum order count!";
            }else{
            // check if this order count already exists in other records
            $sql = "SELECT * FROM coupon_discount WHERE OrderCount='$edit_order_count' AND CouponId <> '$coupon_id'";
            $result = $db->query($sql);
            if($result->num_rows>0){ // means already exists
                $message['order_count'] = "Order count already exists. Consider changing discount!";
            }
        }
        }
        if (empty($edit_discount)) {
            $message['edit_discount'] = "Enter updated discount value!";
        }
        if (empty($message)) {
            $edit_discount = $edit_discount / 100;
            $sql = "UPDATE coupon_discount SET OrderCount='$edit_order_count',NextOrderDiscount='$edit_discount' WHERE CouponId='$coupon_id'";
            $result = $db->query($sql);
            if ($result) {
                echo "<script>
                        Swal.fire({
                            icon: 'info',
                            title: '',
                            html: '<h4>Discount updated!</h4>',
                            showCloseButton: false,
                            showConfirmButton: false,
                            timer: 2000
                        });
                      </script>";
            }
        }
    }
}
?> 
<div class="row">
    <div class="col-6">
        <!--top buttons area-->
        <div class="mb-2">
            <a href="<?= SYS_URL ?>orders/manage.php" class="btn btn-outline-dark"><i class="fas fa-arrow-left "> </i> Back</a>
            <button type="button" class="btn btn-success" <?= $privilege['Add'] == '0' ? 'disabled' : '' ?> data-bs-toggle="modal" data-bs-target="#createDiscount">
                <i class="fas fa-plus"></i>  New Discount
            </button>                                
        </div>

        <!-- Add New Discount Modal -->
        <div class="modal fade" id="createDiscount">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title fs-5">Add New Discount</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                        <div class="modal-body">
                            <label for="order_count" class="col-form-label">Order Count :</label>
                            <input type="number" class="form-control" name="order_count" id="order_count" min="0" max="100" placeholder="Minimum order count">
                            <span class="error_span text-danger"><?= @$message['order_count'] ?></span><br>
                            <label for="new_discount" class="col-form-label">Discount Value (%) :</label>
                            <input type="number" class="form-control" name="new_discount" id="new_discount" min="0" max="100" placeholder="20">
                            <span class="error_span text-danger"><?= @$message['new_discount'] ?></span><br>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" name="action" value="add">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-info">Add Discount</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!--Update Discount Modal--> 
        <div class="modal fade" id="updateDiscount">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title fs-5">Update Existing Discount</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                        <div class="modal-body">
                            <label for="edit_order_count" class="col-form-label">Order Count :</label>
                            <input type="number" class="form-control" name="edit_order_count" id="edit_order_count" min="0" max="100">
                            <span class="error_span text-danger"><?= @$message['edit_order_count'] ?></span><br>
                            <label for="edit_discount" class="col-form-label">Discount Value (%):</label>
                            <input type="number" class="form-control" name="edit_discount" id="edit_discount" min="0" max="100">
                            <span class="error_span text-danger"><?= @$message['edit_discount'] ?></span><br>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" id="coupon_id" name="coupon_id">
                            <input type="hidden" name="action" value="edit">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Discount</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Existing Discounts</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <?php
                $sql = "SELECT * FROM coupon_discount";
                $result = $db->query($sql);
                ?>
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Order Count</th>
                            <th>Discount for next Order</th>                          
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $id = $row['CouponId'];
                                $orderCount = $row['OrderCount'];
                                $orderDiscount = $row['NextOrderDiscount'] * 100;
                                ?>
                                <tr>
                                    <td><?= $orderCount ?></td>
                                    <td><?= $orderDiscount ?>%</td>
                                    <td>
                                        <!--if user do not have the Edit privilege on this module, button is disabled-->
                                        <button type="button" class="btn btn-sm btn-outline-dark" <?= $privilege['Edit'] == '0' ? 'disabled' : '' ?> data-bs-toggle="modal" data-bs-target="#updateDiscount" data-couponid="<?= $id ?>" data-ordercount="<?= $orderCount ?>" data-orderdiscount="<?= $orderDiscount ?>">Edit Discount</button>
                                    </td>
                                </tr>

                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="3">No records found.</td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include '../layouts.php';
?>

<script>
    // use jQuery to pass data to the modal when button is clicked
    $(document).ready(function () {
        $('#updateDiscount').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var coupon_id = button.data('couponid');
            var order_count = button.data('ordercount');
            var order_discount = button.data('orderdiscount');

            var modal = $(this);
            modal.find('.modal-body input#edit_order_count').val(order_count);
            modal.find('.modal-body input#edit_discount').val(order_discount);
            modal.find('.modal-footer input#coupon_id').val(coupon_id);
        });
    });
</script>