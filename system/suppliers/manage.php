<?php
ob_start();
session_start(); // to check permission
include_once '../init.php';

// check permission of the user
$current_url = $_SERVER['REQUEST_URI'];
if (!checkPermission($current_url, $_SESSION['USERID'])) {
    header("Location:../unauthorized.php");
}

$link = "Supplier Management";
$breadcrumb_item = "Supplier";
$breadcrumb_item_active = "Manage";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('4'); // 4 is the module id for Supplier Management
?> 
<div class="row">
    <div class="col-12">
        <a href="<?= SYS_URL ?>suppliers/add.php" class="btn btn-dark mb-2 <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-user-plus"></i> New</a>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Supplier Details</h3>
            </div>

            <div class="card-body table-responsive p-0">
                <?php
                $db = dbConn();
                $sql = "SELECT * FROM suppliers INNER JOIN status ON suppliers.Status = status.StatusId";

                $result = $db->query($sql);
                ?>
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Supplier Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Country</th>
                            <th>Register Date</th>
                            <th>Status</th>
                            <th></th>                          
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?= $row['SupplierName'] ?></td>
                                    <td><?= $row['SupplierEmail'] ?></td>
                                    <td><?= $row['SupplierPhone'] ?></td>
                                    <td><?= $row['Country'] ?></td>
                                    <td><?= $row['RegisterDate'] ?></td>
                                    <td><span class="<?= $row['Status'] == 'Active' ? 'badge badge-success' : 'badge badge-danger' ?>" style="width:80%"><?= $row['Status'] ?></span></td>
                                    <td>
                                        <div class="btn-group btn-group">
                                            <!--<a href="" class="btn btn-info <?= $privilege['Select'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-eye"></i> View</a>-->
                                            <a href="<?= SYS_URL ?>suppliers/edit.php?id=<?= $row['SupplierId'] ?>" class="btn btn-outline-dark <?= $privilege['Edit'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-edit"></i> Edit</a>
                                            <?php
                                            if ($row['Status'] == 'Active') {
                                                ?>
                                                <a href = "<?= SYS_URL ?>suppliers/action.php?action=disable&id=<?= $row['SupplierId'] ?>" onclick = "return confirmDisable();" class = "btn btn-outline-danger <?= $privilege['Delete'] == '0' ? 'disabled' : '' ?>"><i class = "fas fa-ban"></i></a>
                                                <?php
                                            } else {
                                                ?>
                                                <a href = "<?= SYS_URL ?>suppliers/action.php?action=enable&id=<?= $row['SupplierId'] ?>" onclick = "return confirmEnable();" class = "btn btn-outline-success <?= $privilege['Delete'] == '0' ? 'disabled' : '' ?>"><i class = "fas fa-check"></i></a>
                                                <?php
                                            }
                                            ?>
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
        return confirm("Are you sure you want to disable this supplier?");
    }
    function confirmEnable() {
        return confirm("Are you sure you want to enable this supplier?");
    }
</script>