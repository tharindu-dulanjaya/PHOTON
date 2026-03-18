<?php
ob_start();
session_start(); // to check permission
include_once '../init.php';

$db = dbConn();
$link = "Stock History";
$breadcrumb_item = "Inventory";
$breadcrumb_item_active = "History";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('6'); // 6 is the module id for Inventory Management
?> 
<div class="row">
    <div class="col-12">

        <!--Filter section-->
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">            
            <a href="<?= SYS_URL ?>inventory/manage.php" class="btn btn-outline-dark"> <i class="fas fa-arrow-left"> </i> Go Back</a>
            <p class="d-inline-flex gap-1">
                <button class="btn btn-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                    <i class="fas fa-filter"></i> Filter
                </button>                
            </p>
            <div class="collapse" id="collapseFilter">
                <div class="card card-body">
                    <div class="row ">
                        <div class="col-md-3">
                            <div class="input-group mb-1">
                                <label class="input-group-text" for="from_date" >From</label>
                                <input type="date" class="form-control" id="from_date" name="from_date" value="<?= @$from_date ?>">
                            </div>
                            <div class="input-group">
                                <label class="input-group-text" for="to_date" >To</label>
                                <input type="date" class="form-control" id="to_date" name="to_date" value="<?= @$to_date ?>">
                            </div>
                        </div>

                        <div class="col-md-9">   
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <?php
                                    $sql = "SELECT * FROM item_category WHERE status='1'";
                                    $result = $db->query($sql);
                                    ?>
                                    <select class="form-control" name="category_name">
                                        <option value="" readonly>Filter by Category</option>
                                        <?php while ($row = $result->fetch_assoc()) { ?>
                                            <option value="<?= $row['category_name'] ?>" <?= @$category_name == $row['category_name'] ? 'selected' : '' ?>><?= $row['category_name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <?php
                                    $sql = "SELECT * FROM items WHERE status='1'";
                                    $result = $db->query($sql);
                                    ?>
                                    <select class="form-control" name="item_name">
                                        <option value="" readonly>Filter by Machine Name</option>
                                        <?php while ($row = $result->fetch_assoc()) { ?>
                                            <option value="<?= $row['item_name'] ?>" <?= @$item_name == $row['item_name'] ? 'selected' : '' ?>><?= $row['item_name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <?php
                                    $sql = "SELECT * FROM suppliers WHERE Status='1'";
                                    $result = $db->query($sql);
                                    ?>
                                    <select class="form-control" name="SupplierName">
                                        <option value="">Filter by Supplier</option>
                                        <?php while ($row = $result->fetch_assoc()) { ?>
                                            <option value="<?= $row['SupplierName'] ?>" <?= @$SupplierName == $row['SupplierName'] ? 'selected' : '' ?>><?= $row['SupplierName'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-dark mb-2"><i class="fas fa-search"></i> Filter</button>
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
                <h3 class="card-title">All Stock</h3>
            </div>
            
            <div class="card-body table-responsive p-0">
                <?php
                // if someone filter from date, it will be submitted by the form using POST method. then the sql query will be updated with WHERE clause
                $where = null;
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    extract($_POST); // to get data from form fields
                    if (!empty($from_date) && !empty($to_date)) {
                        $where .= " s.purchase_date BETWEEN '$from_date' AND '$to_date' AND";
                    }
                    if (!empty($category_name)) {
                        $where .= " c.category_name = '$category_name' AND";
                    }
                    if (!empty($item_name)) {
                        $where .= " i.item_name = '$item_name' AND";
                    }
                    if (!empty($SupplierName)) {
                        $where .= " x.SupplierName = '$SupplierName' AND";
                    }
                    if (!empty($where)) {
                        $where = substr($where, 0, -3); // removes the last 3 characters (AND part)
                        $where = " WHERE $where"; // final complete WHERE statement
                    }
                }

                $db = dbConn();
                $sql = "SELECT s.qty,s.unit_price,s.purchase_date,i.item_name,c.category_name,x.SupplierName "
                        . "FROM item_stock s "
                        . "INNER JOIN items i on i.id=s.item_id "
                        . "INNER join item_category c ON i.item_category= c.id "
                        . "INNER JOIN suppliers x ON s.supplier_id = x.SupplierId $where ORDER BY s.id DESC";
                $result = $db->query($sql);
                ?>

                <table class="table table-hover text-nowrap" id="myTable">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Unit Price</th>
                            <th>Qty</th>
                            <th>Purchase Date</th>
                            <th>Supplier</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?= $row['item_name'] ?></td>
                                    <td><?= $row['category_name'] ?></td>
                                    <td><?= $row['unit_price'] ?></td>
                                    <td><?= $row['qty'] ?></td>
                                    <td><?= $row['purchase_date'] ?></td>
                                    <td><?= $row['SupplierName'] ?></td>
                                    <td></td>
                                    <td></td>
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