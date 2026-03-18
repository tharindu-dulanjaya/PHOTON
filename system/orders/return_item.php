<?php
ob_start();
include_once '../init.php';

extract($_GET); // receives the order_id and item_id
extract($_POST);

$db = dbConn();

$sql = "SELECT * FROM orders o "
        . "INNER JOIN order_items oi ON oi.order_id=o.id "
        . "INNER JOIN items i ON i.id=oi.item_id "
        . "INNER JOIN customers c ON o.customer_id = c.CustomerId "
        . "INNER JOIN users u ON c.UserId = u.UserId "
        . "INNER JOIN titles t ON u.TitleId = t.Id "
        . "INNER JOIN districts d ON d.Id = u.DistrictId "
        . "LEFT JOIN order_status s ON s.StatusId = o.order_status "
        . "LEFT JOIN payment_methods pm ON pm.PayMethodId = o.payment_method "
        . "WHERE o.id = '$order_id' AND oi.item_id='$item_id' "
        . "ORDER BY o.order_date DESC";
$result = $db->query($sql);
$row = $result->fetch_assoc();

$order_no = $row['order_number'];
$order_status = $row['order_status'];
$item_name = $row['item_name'];
$reg_no = $row['RegNo'];
$title = $row['Title'];
$fname = $row['FirstName'];
$lname = $row['LastName'];
$millName = $row['MillName'];
$addr1 = $row['AddressLine1'];
$addr2 = $row['AddressLine2'];
$city = $row['City'];
$orderStatus = $row['OrderStatus'];
$orderDate = $row['order_date'];
$payMethod = $row['PaymentMethod'];
$delivery_name = $row['delivery_name'];
$delivery_phone = $row['delivery_phone'];
$del_addr1 = $row['delivery_address1'];
$del_addr2 = $row['delivery_address2'];
$del_city = $row['delivery_city'];
$order_notes = $row['order_notes'];

$link = "Return Order #" . $order_no;
$breadcrumb_item = "Order";
$breadcrumb_item_active = "Return Items";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $message = array();

    // check if the items have issued previously
    $sql = "SELECT * FROM order_items_issue WHERE order_id='$order_id' AND item_id='$item_id'";
    $result = $db->query($sql);
    $row = $result->fetch_assoc();
    $issue_id = $row['id']; // order_items_issue id
    $stock_id = $row['stock_id']; // from which stock_id, the item was issued
    $unit_price = $row['unit_price'];

    // check if the serial numbers are true and valid
    $serial_numbers = $_POST['serial_nos'];
    foreach ($serial_numbers as $sno) {
        $sql = "SELECT * FROM issued_serial_numbers WHERE Order_Items_Issue_Id='$issue_id' AND SerialNo='$sno'";
        $result = $db->query($sql);
        if ($result->num_rows <= 0) { // means the serial number is invalid (no records found)
            $message['serial_no'] = "Invalid serial number " . $sno;
        }
    }
    if (empty($message)) {
        // for 'not damaged' items
        if ($return_type <> 'damaged') {
            // subtract the returning qty from the issued qty in 'order_items_issue' table
            $sql = "UPDATE order_items_issue SET issued_qty = COALESCE(issued_qty, 0) - $quantity_return "
                    . "WHERE stock_id='$stock_id' AND order_id='$order_id' AND item_id='$item_id'";
            $db->query($sql);

            // subtract the returning qty from the issued qty in 'order_items' table
            // the stock id in this table is not the exact stock id from which it issued!
            $sql = "UPDATE order_items SET issued_qty = COALESCE(issued_qty, 0) - $quantity_return "
                    . "WHERE order_id='$order_id' AND item_id='$item_id'";
            $db->query($sql);

            // subtract the returning qty from the issued qty in 'item_stock' table
            $sql = "UPDATE item_stock SET issued_qty = COALESCE(issued_qty, 0) - $quantity_return "
                    . "WHERE id='$stock_id'";
            $db->query($sql);

            foreach ($serial_numbers as $sno) {
                // make available the each serial number again (set issued as 0)
                $sql = "UPDATE item_serial_numbers SET Issued='0' WHERE StockId='$stock_id' AND SerialNumber='$sno'";
                $db->query($sql);

                // remove the relevant serial numbers from issued_serial_numbers table
                $sql = "DELETE FROM issued_serial_numbers WHERE Order_Items_Issue_Id='$issue_id' AND SerialNo='$sno'";
                $db->query($sql);
            }
        }
        // have to insert whether it is damaged or not
        // insert return item details in to a new table
        $sql = "INSERT INTO order_return_items(order_id, stock_id, item_id, unit_price, qty, return_date, return_type, return_notes) "
                . "VALUES ('$order_id','$stock_id','$item_id','$unit_price','$quantity_return','$return_date','$return_type','$return_notes')";
        $db->query($sql);

        // to insert each serial number in to returned serial numbers table
        $return_id = $db->insert_id;

        foreach ($serial_numbers as $sno) {
            $sql = "INSERT INTO returned_serial_numbers(ReturnId, SerialNo) VALUES ('$return_id','$sno')";
            $db->query($sql);
        }
        
        // update order status as returned
        $sql = "UPDATE orders SET order_status = '8' WHERE id = '$order_id'";
        $result = $db->query($sql);
        if ($result) {
            echo "<script>
            Swal.fire({
                icon: 'success',
                title: '',
                html: '<h4>Items returned successfully</h4>',
                showCloseButton: false,
                showConfirmButton: false
            }).then(function() {
                window.location.href = 'manage.php';
            });
          </script>";
        }
    }
}
?> 

<a href="<?= SYS_URL ?>orders/manage.php" class="btn btn-outline-dark mb-2"><i class="fas fa-arrow-left "> </i> Back</a>
<div class="row">
    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title text-primary">Customer Details</h3>
            </div>
            <div class="card-body">
                Registration # <b><?= $reg_no ?></b><br><br>
                <?= $title ?> <?= $fname ?> <?= $lname ?> <br>
                <?= $millName ?><br>
                <?= $addr1 ?>, <?= $addr2 ?><br>
                <?= $city ?><br>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title text-primary">Order Details</h3>
            </div>
            <div class="card-body">
                Current Status :  <b><?= $orderStatus ?></b><br><br>
                Order Number # <b><?= $order_no ?></b><br>
                Order Date : <b><?= $orderDate ?></b><br>
                Payment Method : <b><?= $payMethod ?></b><br>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title text-primary">Delivery Details</h3>
            </div>
            <div class="card-body">
                <?= $delivery_name ?><br>
                <?= $delivery_phone ?><br>
                <?= $del_addr1 ?>, <?= $del_addr2 ?><br> 
                <?= $del_city ?><br>
                <p style="color:red;">Customer Notes : <?= $order_notes ?></p>
            </div>
        </div>    
    </div>
    <div class="col-8">
        <div class="card card-primary sticky-top">            
            <div class="card-header">
                <h3 class="card-title">Return Item Details</h3>
            </div>

            <form id='returnForm' action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" novalidate>
                <div class="card-body">
                    <div class="row mb-3">
                        <label for="item_name" class="col-3 col-form-label">Item Name : </label>
                        <div class="col-9">
                            <input type="text" readonly class="form-control-plaintext" name="item_name" id="item_name" value="<?= $item_name ?>" >
                        </div>
                    </div>
                    <div class="row">                                                    
                        <label for="quantity_return" class="col-3 col-form-label">Return Quantity : </label>
                        <div class="col-3">
                            <!--max should be the issued qty-->
                            <input type="number" class="form-control border border-1 border-dark-subtle" name="quantity_return" id="qty" placeholder="Return Quantity" min="1" max="<?= $row['issued_qty'] ?>" required>
                            <span class="error_span text-danger"><?= @$message['quantity_return'] ?></span>
                        </div>
                        <div class="serial-numbers-container col-6">
                            <!--relevant serial number fields are populated here according to qty-->
                            <span class="error_span text-danger"><?= @$message['serial_no'] ?></span>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="form-group col-6">
                            <?php
                            $sql = "SELECT * FROM return_reasons";
                            $result = $db->query($sql);
                            ?>
                            <label for="return_type">Reason for return</label>
                            <select name="return_type" id="return_type" class="form-control border border-1 border-dark-subtle">
                                <option value="">Return Type</option>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <option value="<?= $row['Value'] ?>"> <?= $row['Reason'] ?> </option>
                                    <?php
                                }
                                ?>
                            </select>                                    
                            <span class="error_span text-danger"><?= @$message['return_type'] ?></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-6">
                            <label for="return_date">Return Date:</label>
                            <input type="date" name="return_date" id="return_date" class="form-control" value="<?= @$return_date ?>" required>
                            <span class="error_span text-danger mt-4"><?= @$message['return_date'] ?></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-6">
                            <label for="return_notes">Comments</label>
                            <textarea type="text" name="return_notes" id="return_notes" class="form-control"><?= @$return_notes ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <input type="hidden" id="item_id" name="item_id" value="<?= $item_id ?>">
                    <input type="hidden" id="order_id" name="order_id" value="<?= $order_id ?>">
                    <input type="submit" value="Handle Return" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include '../layouts.php';
?>

<script>

    $(document).ready(function () {
        // add serial number fields to the serial number container
        function addSerialNumberFields(quantity, container) {
            container.html(''); // clear existing fields

            // add same number of serial_no fields as the qty
            for (let i = 0; i < quantity; i++) {
                container.append('<div class="form-group"><input type="text" name="serial_nos[]" class="form-control serial_number" placeholder="Serial Number ' + (i + 1) + '" required></div>');
            }
        }

        // when the qty changes in the #returnForm
        $('#returnForm').on('change', '#qty', function () {
            var quantity = $(this).val();
            var container = $(this).closest('.row').find('.serial-numbers-container');
            addSerialNumberFields(quantity, container);
        });

        // form submission validation
        $('#returnForm').on('submit', function (e) {
            var valid = true; // to check if the form inputs are valid. initially valid = true

            if ($('#item_name').val().trim() === '') {
                valid = false;
                Swal.fire({
                    icon: 'info',
                    title: 'Item Name is required',
                    showConfirmButton: false,
                    timer: 1500
                });
                return false; // stop further execution of code
            }

            if ($('#qty').val().trim() === '') {
                valid = false;
                Swal.fire({
                    icon: 'info',
                    title: 'Return Quantity is required',
                    showConfirmButton: false,
                    timer: 1500
                });
                return false; // stop further execution of code
            }

            // check if the quantity matches the maximum value
            var quantity = $('#qty').val().trim();
            var maxQuantity = $('#qty').attr('max');
            if (quantity > maxQuantity) {
                valid = false;
                Swal.fire({
                    icon: 'warning',
                    title: 'Quantity can not be greater than ' + maxQuantity,
                    showConfirmButton: false,
                    timer: 1500
                });
                return false;
            }

            $('.serial_number').each(function () {
                if ($(this).val().trim() === '') {
                    valid = false;
                    Swal.fire({
                        icon: 'info',
                        title: 'All Serial Numbers are required',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    return false;
                }
            });

            if ($('#return_type').val().trim() === '') {
                valid = false;
                Swal.fire({
                    icon: 'info',
                    title: 'Select the Return Type',
                    showConfirmButton: false,
                    timer: 1500
                });
                return false;
            }

            if ($('#return_date').val().trim() === '') {
                valid = false;
                Swal.fire({
                    icon: 'info',
                    title: 'Return Date is required',
                    showConfirmButton: false,
                    timer: 1500
                });
                return false; // stop further execution of code
            }

            if (!valid) {
                e.preventDefault(); // if valid is false, prevent the form submission 
            }
        });
    });

</script>