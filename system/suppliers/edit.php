<?php
ob_start();
include_once '../init.php';

$db = dbConn();

$link = "Supplier Management";
$breadcrumb_item = "Suppliers";
$breadcrumb_item_active = "Update";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('4'); // 4 is the module id for Supplier Management

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    extract($_GET); // get the id of the supplier from the url
    $sql = "SELECT * FROM suppliers WHERE SupplierId = '$id'";
    $result = $db->query($sql);
    $row = $result->fetch_assoc();

    $supplier_id = $row['SupplierId']; //to send in a hidden variable when submitting the form
    $supplier_name = $row['SupplierName'];
    $email = $row['SupplierEmail'];
    $telephone = $row['SupplierPhone'];
    $address = $row['Address'];
    $country = $row['Country'];
    $reg_date = $row['RegisterDate'];
    $status = $row['Status'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);
    $supplier_name = dataClean($supplier_name);
    $email = dataClean($email);
    $telephone = dataClean($telephone);
    $address = dataClean($address);
    $country = dataClean($country);

    $message = array();

    if (empty($supplier_name)) {
        $message['supplier_name'] = "The Supplier Name cannot be empty!";
    } else {
        if (ctype_alpha(str_replace(' ', '', $supplier_name)) === false) {
            $message['supplier_name'] = "Only letters and white spaces are allowed";
        } else {
            // check if the supplier name already exists
            $sql = "SELECT * FROM suppliers WHERE SupplierName='$supplier_name' AND SupplierId <> '$supplier_id'";
            $result = $db->query($sql);
            if ($result->num_rows > 0) { // means already exists
                $message['supplier_name'] = "Supplier Name already exists!";
            }
        }
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message['email'] = "Invalid Email Address...!";
    } else {
        //check if the email address already exists or not
        $sql = "SELECT * FROM suppliers WHERE SupplierEmail='$email' AND SupplierId <> '$supplier_id'";
        $result = $db->query($sql);

        if ($result->num_rows > 0) {
            $message['email'] = "This Email address already exists...!";
        }
    }
    if (empty($telephone)) {
        $message['telephone'] = "Telephone Number cannot be empty!";
    }
    if (empty($address)) {
        $message['address'] = "Address cannot be empty!";
    }
    if (empty($country)) {
        $message['country'] = "Country cannot be empty!";
    }
    if (empty($reg_date)) {
        $message['reg_date'] = "Please enter the Registered Date!";
    }
    if (empty($status)) {
        $message['status'] = "Select the status of this supplier";
    }

    if (empty($message)) {
        $sql = "UPDATE suppliers SET SupplierName='$supplier_name',SupplierEmail='$email',SupplierPhone='$telephone',Address='$address',Country='$country',RegisterDate='$reg_date',Status='$status' WHERE SupplierId='$supplier_id'";
        $db->query($sql);

        header("Location:manage.php");
    }
}
?>

<div class="row">
    <div class="col-12">

        <a href="manage.php" class="btn btn-dark mb-2"><i class="fas fa-users"></i> View All</a>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Update Supplier</h3>
            </div>

            <!--Add novalidate attribute to skip browser validations-->
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="supplier_name">Supplier Name</label>
                            <input type="text" class="form-control" id="supplier_name" name="supplier_name" placeholder="Enter Supplier Name" value="<?= $supplier_name ?>">
                            <span class="error_span text-danger"><?= @$message['supplier_name'] ?></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" name="email" id="email" placeholder="Email" value="<?= $email ?>" required>
                            <span class="error_span text-danger"><?= @$message['email'] ?></span><br>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="telephone">Phone Number</label>
                            <input type="text" class="form-control border border-1 border-dark-subtle" name="telephone" id="telephone" placeholder="Telephone Number" value="<?= $telephone ?>" required>
                            <span class="error_span text-danger"><?= @$message['telephone'] ?></span><br>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-8">
                            <label for="address">Address</label>
                            <input type="text" class="form-control" name="address" id="address" placeholder="Address" value="<?= $address ?>" required>
                            <span class="error_span text-danger"><?= @$message['address'] ?></span><br>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="address">Country</label>
                            <input type="text" class="form-control" name="country" id="country" placeholder="Country" value="<?= $country ?>" required>
                            <span class="error_span text-danger"><?= @$message['country'] ?></span><br>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="app_date">Register Date</label>
                            <input type="date" class="form-control" max="<?= date('Y-m-d'); ?>" id="reg_date" name="reg_date" value="<?= $reg_date ?>">
                            <span class="error_span text-danger"><?= @$message['reg_date'] ?></span>
                        </div>
                        <div class="form-group col-md-4 ">
                            <?php
                            $sql = "SELECT * FROM status";
                            $result = $db->query($sql);
                            ?>
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="" disabled>Select Status</option>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <!--use ternary operator to insert the selected attribute-->
                                    <option value="<?= $row['StatusId'] ?>" <?= $status == $row['StatusId'] ? 'selected' : '' ?>> <?= $row['Status'] ?> </option>
                                    <?php
                                }
                                ?>
                            </select>
                            <span class="error_span text-danger mt-4"><?= @$message['status'] ?></span><br>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <!--$SupplierId must be passed when submitting the form by POST method to use the variable in the sql queries-->
                    <input type="hidden" name="supplier_id" value="<?= $supplier_id ?>">
                    <button type="submit" class="btn btn-primary" <?= $privilege['Edit'] == '0' ? 'disabled' : '' ?>>Update Supplier</button>
                </div>
            </form>

        </div>
    </div>
</div>


<?php
$content = ob_get_clean();
include '../layouts.php';
?>

