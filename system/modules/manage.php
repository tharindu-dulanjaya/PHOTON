<?php
ob_start();
session_start(); // to check permission
include_once '../init.php';

// check permission of the user
$current_url = $_SERVER['REQUEST_URI'];
if (!checkPermission($current_url, $_SESSION['USERID'])) {
    header("Location:../unauthorized.php");
}

$link = "Modules Management";
$breadcrumb_item = "Modules";
$breadcrumb_item_active = "Manage";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('8'); // 8 is the module id for Module Management
?> 
<div class="row">
    <div class="col-12">
        <a href="<?= SYS_URL ?>modules/add.php" class="btn btn-dark mb-2 <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-plus"></i>  New Module</a>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Dashboard Tabs (Modules)</h3>
            </div>            

            <div class="card-body table-responsive p-0">
                <?php
                $db = dbConn();
                $sql = "SELECT * FROM modules INNER JOIN status ON modules.Status = status.StatusId";

                $result = $db->query($sql);
                ?>
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Module Name</th>
                            <th>Path</th>
                            <th>File Name</th>
                            <th>Module Icon</th>
                            <th>Index</th>
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
                                    <td><?= $row['Name'] ?></td>
                                    <td><?= $row['Path'] ?></td>
                                    <td><?= $row['File'] ?></td>
                                    <td><i class="<?= $row['Icon'] ?>"></i></td>
                                    <td><?= $row['Idx'] ?></td>
                                    <td>
                                        <span class="<?= $row['Status'] == 'Active' ? 'badge badge-success' : 'badge badge-danger' ?>" style="width:80%"><?= $row['Status'] ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group">
                                            <a href="<?= SYS_URL ?>modules/edit.php?id=<?= $row['Id'] ?>" class="btn btn-outline-dark <?= $privilege['Edit'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-edit"></i>&nbsp; Edit</a>
                                            <?php
                                            //disable button is diabled for modules & employee management
                                            if ($row['Status'] == 'Active') {
                                                if ($row['Id'] != '1' && $row['Id'] != '8') {
                                                    ?>
                                                    <a href = "<?= SYS_URL ?>modules/action.php?action=disable&moduleid=<?= $row['Id'] ?>" onclick = "return confirmDisable();" class = "btn btn-outline-danger <?= $privilege['Delete'] == '0' ? 'disabled' : '' ?>"><i class = "fas fa-ban"></i></a>
                                                    <?php
                                                }
                                                ?>
                                                <?php
                                            } else {
                                                ?>
                                                <a href = "<?= SYS_URL ?>modules/action.php?action=enable&moduleid=<?= $row['Id'] ?>" onclick = "return confirmEnable();" class = "btn btn-outline-success <?= $privilege['Delete'] == '0' ? 'disabled' : '' ?>"><i class = "fas fa-check"></i></a>
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
        return confirm("Are you sure to disable this customer account?");
    }
    function confirmEnable() {
        return confirm("Are you sure to enable this customer account?");
    }
</script>