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
$link = "Product Management";
$breadcrumb_item = "Products";
$breadcrumb_item_active = "Manage";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('9'); // 9 is the module id for Product Management
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
            <a href="<?= SYS_URL ?>products/add.php" class="btn btn-info <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-plus "> </i> New Product</a>
            <a href="<?= SYS_URL ?>products/category.php" class="btn btn-primary <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-sitemap "> </i> Product Category</a>
            <div class="collapse" id="collapseFilter">
                <div class="card card-body">
                    <div class="row ">                        
                        <div class="col-12">   
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <input type="text" class="form-control" id="product_name" name="product_name" placeholder="Filter by Product Name" value="<?= @$_POST['product_name'] ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <input type="text" class="form-control" id="model_no" name="model_no" placeholder="Filter by Model Number" value="<?= @$_POST['model_no'] ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <?php
                                    $sql = "SELECT * FROM item_category WHERE status='1'";
                                    $result = $db->query($sql);
                                    ?>
                                    <select class="form-control" name="product_category">
                                        <option value="" readonly>Filter by Product Category</option>
                                        <?php while ($row = $result->fetch_assoc()) { ?>
                                            <option value="<?= $row['id'] ?>" <?= @$_POST['product_category'] == $row['id'] ? 'selected' : '' ?>><?= $row['category_name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="text-end">
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
                <h3 class="card-title">Product Details</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <?php
                //this where part executes with the filter button clicked(POST)
                $where = null;
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    extract($_POST); // to get data from form fields
                    
                    if (!empty($product_name)) {
                        $where .= " i.item_name LIKE '%$product_name%' AND";
                    }
                    if (!empty($model_no)) {
                        $where .= " i.model_no LIKE '%$model_no%' AND";
                    }
                    if (!empty($product_category)) {
                        $where .= " i.item_category = '$product_category' AND";
                    }
                    if (!empty($where)) {
                        $where = substr($where, 0, -3); // removes the last 3 characters (AND part)
                        $where = " WHERE $where"; // final complete WHERE statement
                    }
                } else {
                    // must required where part. this executes when the page loads without post(not filtered)
                    $where = null;
                }
                $sql = "SELECT i.item_image,"
                        . " i.id,"
                        . " i.item_name,"
                        . " i.model_no,"
                        . " c.category_name,"
                        . " s.Status"
                        . " FROM items i"
                        . " INNER JOIN item_category c ON c.id = i.item_category"
                        . " INNER JOIN status s ON s.StatusId = i.status"
                        . " $where "
                        . " ORDER BY i.item_category";
                $result = $db->query($sql);
                ?>
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Name</th>
                            <th>Model Number</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th></th>
                            <th></th>                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr >
                                    <td> 
                                        <img src="../../uploads/products/<?= $row['item_image'] ?>" width="100px" height="100px" alt="">
                                    </td>
                                    <td><?= $row['item_name'] ?></td>
                                    <td><?= $row['model_no'] ?></td>
                                    <td><?= $row['category_name'] ?></td>
                                    <td><span class="<?= $row['Status'] == 'Active' ? 'badge badge-success' : 'badge badge-danger' ?>" style="width:80%"><?= $row['Status'] ?></span></td>                                      
                                    <td><a href="<?= SYS_URL ?>products/edit.php?pid=<?= $row['id'] ?>" class="btn btn-primary <?= $privilege['Edit'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-edit"></i> Edit</a></td>
                                    <td>
                                        <?php
                                        if ($row['Status'] == 'Active') {
                                            ?>
                                            <a href="<?= SYS_URL ?>products/action.php?action=disable&pid=<?= $row['id'] ?>" onclick="return confirmDisable();" class="btn btn-danger <?= $privilege['Delete'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-ban"></i> Disable</a>
                                            <?php
                                        } else {
                                            ?>
                                            <a href="<?= SYS_URL ?>products/action.php?action=enable&pid=<?= $row['id'] ?>" onclick="return confirmEnable();" class="btn btn-success <?= $privilege['Delete'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-eye"></i> Enable</a>
                                            <?php
                                        }
                                        ?>
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
