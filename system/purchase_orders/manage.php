<?php
ob_start();
session_start(); // to check permission
include_once '../init.php';

// check permission of the user
$current_url = $_SERVER['REQUEST_URI'];
if (!checkPermission($current_url, $_SESSION['USERID'])) {
    header("Location:../unauthorized.php");
}

$db = dbConn();
$link = "Purchase Order Management";
$breadcrumb_item = "Purchase Order";
$breadcrumb_item_active = "Manage";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('10'); // 10 is the module id for PO Management
?> 
<div class="row">
    <div class="col-12">

        <!--Filter section-->
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">            
            <p class="d-inline-flex gap-1">
                <button class="btn btn-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </p>
            <a href="<?= SYS_URL ?>purchase_orders/create.php" class="btn btn-info <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-plus-circle"></i> Create PO</a>
            <div class="collapse" id="collapseFilter">
                <div class="card card-body">
                    <div class="row ">
                        <div class="col-md-3">
                            <div class="input-group mb-1">
                                <label class="input-group-text" for="from_date" >From</label>
                                <input type="date" class="form-control" id="from_date" name="from_date" value="<?= @$_POST['from_date'] ?>">
                            </div>
                            <div class="input-group">
                                <label class="input-group-text" for="to_date" >To</label>
                                <input type="date" class="form-control" id="to_date" name="to_date" value="<?= @$_POST['to_date'] ?>">
                            </div>
                        </div>
                        <div class="col-md-9">   
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <input type="text" class="form-control" id="purchase_order_no" name="purchase_order_no" placeholder="Filter By Purchase Order #" value="<?= @$_POST['purchase_order_no'] ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <?php
                                    $sql = "SELECT * FROM suppliers WHERE Status='1'";
                                    $result = $db->query($sql);
                                    ?>
                                    <select class="form-control" name="supplier">
                                        <option value="" readonly>Filter By Supplier</option>
                                        <?php
                                        while ($row = $result->fetch_assoc()) {
                                            ?>
                                            <option value="<?= $row['SupplierId'] ?>" <?= @$_POST['supplier'] == $row['SupplierId'] ? 'selected' : '' ?>><?= $row['SupplierName'] ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>                                
                                <div class="form-group col-md-4">
                                    <?php
                                    $sql = "SELECT * FROM purchase_order_status";
                                    $result = $db->query($sql);
                                    ?>
                                    <select class="form-control" name="po_status">
                                        <option value="" readonly>Purchase Order Status</option>
                                        <?php
                                        while ($row = $result->fetch_assoc()) {
                                            ?>
                                            <option value="<?= $row['PO_StatusId'] ?>" <?= @$_POST['po_status'] == $row['PO_StatusId'] ? 'selected' : '' ?>><?= $row['PurchaseOrderStatus'] ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-9">
                                </div>
                                <div class="form-group col-md-3">
                                    <button type="submit" class="btn btn-dark float-right"><i class="fas fa-search"></i> Filter</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!--Filter end-->

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Purchase Orders</h3>
            </div>

            <div class="card-body table-responsive p-0">
                <?php
                // if someone filter from date, it will be submitted by the form using POST method. then the sql query will be updated with WHERE clause
                $where = null;
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    extract($_POST); // to get data from form fields
                    if (!empty($from_date) && !empty($to_date)) {
                        $where .= " po.PO_Date BETWEEN '$from_date' AND '$to_date' AND";
                    }
                    if (!empty($purchase_order_no)) {
                        $where .= " po.PO_Number LIKE '%$purchase_order_no%' AND";
                    }
                    if (!empty($supplier)) {
                        $where .= " po.SupplierId LIKE '%$supplier%' AND";
                    }
                    if (!empty($po_status)) {
                        $where .= " po.PO_Status = '$po_status' AND";
                    }
                    if (!empty($where)) {
                        $where = substr($where, 0, -3); // removes the last 3 characters (AND part)
                        $where = " WHERE $where"; // final complete WHERE statement
                    }
                }

                $db = dbConn();
                $sql = "SELECT * "
                        . "FROM purchase_orders po "
                        . "INNER JOIN suppliers sup ON sup.SupplierId = po.SupplierId "
                        . "INNER JOIN purchase_order_status pos ON pos.PO_StatusId = po.PO_Status "
                        . "$where "
                        . "ORDER BY po.PO_Id DESC";
                $result = $db->query($sql);
                ?>

                <table class="table table-hover text-nowrap" id="myTable">
                    <thead>
                        <tr>
                            <th>Purchase Order #</th>
                            <th>Created on</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th>Expected Delivery</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?= $row['PO_Number'] ?></td>
                                    <td><?= $row['PO_Date'] ?></td>
                                    <td><?= $row['SupplierName'] ?></td>
                                    <td><span class="
                                        <?php
                                        // different labels according to the PO status
                                        if ($row['PO_Status'] == '1') { //pending
                                            echo 'badge badge-warning';
                                        } elseif ($row['PO_Status'] == '2') { //accepted
                                            echo 'badge badge-info';
                                        } elseif ($row['PO_Status'] == '3') { //rejected
                                            echo 'badge badge-dark';
                                        } elseif ($row['PO_Status'] == '4') { //paid
                                            echo 'badge badge-primary';
                                        } elseif ($row['PO_Status'] == '5') { //payment confirmed
                                            echo 'badge badge-success';
                                        } elseif ($row['PO_Status'] == '6') { //payment failed
                                            echo 'badge badge-danger';
                                        }
                                        ?>
                                              " style="width: 75%"><?= $row['PurchaseOrderStatus'] ?></span></td>
                                    <td><?= $row['ExpectedDelivery'] ?></td>
                                    <td>
                                        <div class="btn-group btn-group">
                                            <!--display different views according to the purchase order status-->
                                            <a href="
                                            <?php
                                            if ($row['PO_Status'] == '1') { //still pending
                                                echo "pending_po_details.php?po_id=" . $row['PO_Id'];
                                            } elseif ($row['PO_Status'] == '2') { // po accepted
                                                echo "accepted_po_details.php?po_id=" . $row['PO_Id'];
                                            } elseif ($row['PO_Status'] == '3') { // po rejected
                                                echo "rejected_po_details.php?po_id=" . $row['PO_Id'];
                                            } elseif ($row['PO_Status'] == '4') { // paid
                                                echo "paid_po_details.php?po_id=" . $row['PO_Id'];
                                            } elseif ($row['PO_Status'] == '5') { // payment confirmed
                                                echo "paid_po_details.php?po_id=" . $row['PO_Id'];
                                            } elseif ($row['PO_Status'] == '6') { // payment failed
                                                echo "paid_po_details.php?po_id=" . $row['PO_Id'];
                                            }
                                            ?>
                                               " class="btn btn-primary <?= $privilege['Select'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-eye"></i> View</a>
                                            <a href="edit_details.php?po_id=<?= $row['PO_Id'] ?>" class="btn btn-outline-dark <?= $privilege['Edit'] == '0' ? 'disabled' : '' ?> <?= $row['PO_Status'] == '1' ? '' : 'disabled' ?>  " readonly><i class="fas fa-edit"></i> Edit</a>
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
    </div>
</div>
<?php
$content = ob_get_clean();
include '../layouts.php';
?>

<script>
    function confirmDisable() {
        return confirm("Are you sure you want to disable this product?");
    }
    function confirmEnable() {
        return confirm("Are you sure you want to enable this product?");
    }
</script>