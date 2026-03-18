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
$link = "Inventory / Stock";
$breadcrumb_item = "Inventory";
$breadcrumb_item_active = "Manage";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('6'); // 6 is the module id for Inventory Management
?> 
<div class="row">
    <div class="col-12">

        <p class="d-inline-flex gap-1">
            <button class="btn btn-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter"><i class="fas fa-filter"></i> 
                Filter
            </button>                
        </p>
        <a href="<?= SYS_URL ?>inventory/add_stock.php" class="btn btn-info <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-plus-circle"></i> Add New Stock</a>
        <a href="<?= SYS_URL ?>inventory/stock_history.php" class="btn btn-primary <?= $privilege['Select'] == '0' ? 'disabled' : '' ?>"> View Stock History</a>

        <!--Filter section-->
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">            
            <div class="collapse" id="collapseFilter">
                <div class="card card-body">   
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
                        
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-dark mb-2"><i class="fas fa-search"></i> Filter</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!--Filter end-->

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Stock Details</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body table-responsive p-0">
                <?php
                // if someone filter from date, it will be submitted by the form using POST method. then the sql query will be updated with WHERE clause
                $where = null;
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    extract($_POST); // to get data from form fields
                    if (!empty($category_name)) {
                        $where .= " c.category_name = '$category_name' AND";
                    }
                    if (!empty($item_name)) {
                        $where .= " i.item_name = '$item_name' AND";
                    }
                    if (!empty($where)) {
                        $where = substr($where, 0, -3); // removes the last 3 characters (AND part)
                        $where = " WHERE $where"; // final complete WHERE statement
                    }
                }

                $db = dbConn();
                $sql = "SELECT s.qty,"
                        . "s.unit_price,"
                        . "s.item_id,"
                        . "s.purchase_date,"
                        . "i.item_name,"
                        . "c.category_name,"
                        . "c.minimum_reorder_level,"
                        . "x.SupplierName,"
                        . "SUM(s.qty - s.issued_qty) as available_qty "
                        . "FROM item_stock s "
                        . "INNER JOIN items i on i.id=s.item_id "
                        . "INNER JOIN item_category c ON i.item_category= c.id "
                        . "INNER JOIN suppliers x ON s.supplier_id = x.SupplierId "
                        . "$where "
                        . "GROUP BY s.item_id, s.unit_price "
                        . "ORDER BY c.category_name, i.item_name, s.unit_price ASC";
                $result = $db->query($sql);
                ?>

                <table class="table table-hover text-nowrap" id="myTable">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Item Name</th> 
                            <th>Stock Status</th>
                            <th>Remaining Stock</th>                           
                            <th>Unit Price</th>
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
                                    <td><?= $row['category_name'] ?></td>
                                    <td><?= $row['item_name'] ?></td>  
                                    <td>
                                        <?php
                                        if ($row['available_qty'] >= $row['minimum_reorder_level']) {
                                            echo "<span class='badge badge-success' style='width:60%'>Good</span>";
                                        }elseif ($row['available_qty'] == 0) {
                                            echo "<span class='badge badge-danger' style='width:60%'>Out of Stock</span>";
                                        } else {
                                            echo "<span class='badge badge-warning' style='width:60%'>Low Stock</span>";
                                        }
                                        ?>
                                    </td>
                                    <td><?= $row['available_qty'] ?></td>
                                    <td><?= $row['unit_price'] ?></td>                                    
                                    <td>
                                        <?php
                                        // show a re-order button if available qty is lessthan reorder level
                                        if ($row['available_qty'] < $row['minimum_reorder_level']) {
                                            ?>
                                            <a href="<?= SYS_URL ?>purchase_orders/create.php?itemid=<?= $row['item_id'] ?>" class="text-danger">Re-Order Now</a>
                                            <?php
                                        }
                                        ?>
                                    </td>
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