<?php
ob_start();
include_once '../init.php';
$db = dbConn();

$link = "Add New Stock";
$breadcrumb_item = "Inventory";
$breadcrumb_item_active = "Add Stock";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('6');

extract($_POST);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // input fields are already validated for not empty before submitting the form in frontend using js
    $message = array();

    $item_ids = $_POST['item_id'];
    $qtys = $_POST['qty'];
    $unit_prices = $_POST['unit_price'];
    $serial_numbers = $_POST['serial_nos'];

    foreach ($item_ids as $key => $value) {
        if (empty($item_ids[$key])) {
            $message['item_id'] = "Item should not be blank!";
        }
        if (empty($qtys[$key])) {
            $message['qty'] = "Please select the Quantity!";
        }
        if (empty($unit_prices[$key])) {
            $message['unit_price'] = "Please enter Unit Prices!";
        }
        if (empty($serial_numbers[$key])) {
            $message['serial_nos'] = "The Serial Numbers should not be blank...!";
        }
    }
    if (empty($purchase_date)) {
        $message['purchase_date'] = "Please select the Purchase date!";
    }
    if (empty($supplier_id)) {
        $message['supplier_id'] = "Please select the Supplier";
    }
    // if there are no errors
    if (empty($message)) {

        $serial_idx = 0; // serial no index

        foreach ($item_ids as $key => $item_id) { // key has 0,1,2,..
            $q = $qtys[$key];
            $price = $unit_prices[$key];
            $sql = "INSERT INTO item_stock(item_id, qty, unit_price, purchase_date, supplier_id) "
                    . "VALUES ('$item_id','$q','$price','$purchase_date','$supplier_id')";
            $db->query($sql);

            // get the last inserted id
            $stock_id = $db->insert_id;

            // Insert each serial number into the item_serial_numbers table
            // all the serial numbers are in the same array. so need to insert based on the qty of each item
            for ($i = 0; $i < $q; $i++) {
                $serial_number = $serial_numbers[$serial_idx++];
                $sql = "INSERT INTO item_serial_numbers (StockId, ItemId, SerialNumber) "
                        . "VALUES ('$stock_id', '$item_id', '$serial_number')";
                $db->query($sql);
            }
        }
        header("Location: manage.php");
        exit; // to ensure no php code is executed after the redirect
    }
}
?>
<div class="row">
    <div class="col-12">
        <a href="manage.php" class="btn btn-dark mb-2"><i class="fas fa-warehouse">  </i> View Stock</a>
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Add Stock</h3>
            </div>
            <div class="card-body">
                <form id="add_stock_form" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                    <div class="row mb-3">
                        <div class="col-md-4 form-group">
                            <label for="purchase_date">Purchase Date:</label>
                            <input type="date" name="purchase_date" id="purchase_date" max="<?= date('Y-m-d'); ?>" class="form-control" value="<?= @$purchase_date ?>" required>
                            <span class="error_span text-danger mt-4"><?= @$message['purchase_date'] ?></span><br>
                        </div>
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
                            <span class="error_span text-danger mt-4"><?= @$message['supplier_id'] ?></span><br>
                        </div>
                    </div>

                    <table class="table table-light" id="items">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Serial Numbers</th>
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
                                                ?>
                                                <option value="<?= $row['id'] ?>"><?= $row['item_name'] ?></option>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </select>                                    
                                </td>
                                <td>
                                    <input type="number" name="qty[]" id="qty" class="form-control qty" min="1" max="100" onfocus="this.removeAttribute('readonly');" onblur="this.setAttribute('readonly', true);" required>                                    
                                </td>
                                <td>
                                    <div class="input-group col-10">
                                        <span class="input-group-text">Rs. </span>
                                        <input type="number" id="unit_price" name="unit_price[]" class="form-control" min="100">
                                        <span class="input-group-text">.00</span>                                        
                                    </div>
                                </td>
                                <td>
                                    <div class="serial-numbers-container"></div>
                                </td>
                                <td>
                                    <i id="removeBtn" class="fas fa-trash" > </i>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mb-5">
                        <button type="button" id="addBtn" class="btn btn-dark"><i class="fas fa-plus"></i> Add item </button>
                    </div>
                    <input type="submit" value="Add to Inventory" class="btn btn-primary" <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>>
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
            newRow.find('.serial-numbers-container').html(''); // clear serial number fields

            // make the delete button visible for the new row
            newRow.find('#removeBtn').show();

            // append the cloned row to the table body
            tableBody.append(newRow);
        }

        function removeItems(button) {
            var row = $(button).closest('tr');  // select the row which includes the clicked remove button
            row.remove();
        }

        // add serial number fields to the serial number container
        function addSerialNumberFields(quantity, container) {
            container.html(''); // clear existing fields

            // add same number of serial_no fields as the qty
            for (let i = 0; i < quantity; i++) {
                container.append('<div class="form-group"><input type="text" name="serial_nos[]" class="form-control serial_number" placeholder="Serial Number ' + (i + 1) + '" required></div>');
            }
        }

        // execute addItems() when #addBtn is clicked
        $('#addBtn').click(addItems);

        // execute removeItems() when #removeBtn is clicked
        $('#items').on('click', '#removeBtn', function () {
            removeItems(this);
        });

        // when the qty changes in the #items table
        $('#items').on('change', '.qty', function () {
            var quantity = $(this).val();
            var container = $(this).closest('tr').find('.serial-numbers-container');
            addSerialNumberFields(quantity, container);
        });

        // when click on the form submit button, this functions checks if all the fields are filled
        $('#add_stock_form').submit(function (event) {

            // to check if the form inputs are valid. initially valid = true
            var valid = true;

            // check if purchase date and supplier is not empty
            if ($('#purchase_date').val().trim() === '') {
                valid = false;
                Swal.fire({
                    icon: 'info',
                    title: 'Select the Purchase date',
                    showConfirmButton: false,
                    timer: 1500
                });
                return false;
            }
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

            // iterates over each item-row
            $('.items-row').each(function () {
                var item = $(this).find('#item_id').val();
                var quantity = $(this).find('.qty').val();
                var unitPrice = $(this).find('#unit_price').val();
                var serialNumbers = $(this).find('.serial_number');

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

                // check if no of serial numbers match to the quantity
                if (serialNumbers.length != quantity) {
                    valid = false; // if not, false
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: 'The number of serial numbers must match the quantity!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    return false; // stop further execution for this iteration
                }
                // iterates over each serial number input field & check if all fields are filled
                serialNumbers.each(function () {
                    if ($(this).val().trim() === '') { //check if any field is empty. (contain only whitespace)
                        valid = false; // mark form inputs as invalid
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: 'All serial number fields must be filled!',
                            showConfirmButton: false,
                            timer: 2500
                        });
                        return false; // stop further execution
                    }
                });
            });
            if (!valid) {
                event.preventDefault();  // if valid is false, prevent the form submission 
            }
        });
    });
</script>
