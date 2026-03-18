<?php
ob_start();
session_start(); // to check permission
include_once '../init.php';

// check permission of the user
$current_url = $_SERVER['REQUEST_URI'];
if (!checkPermission($current_url, $_SESSION['USERID'])) {
    header("Location:../unauthorized.php");
}

$link = "Technician Management";
$breadcrumb_item = "Technicians";
$breadcrumb_item_active = "Manage";

$db = dbConn();

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('11'); // Technician Management
?> 
<div class="row">
    <div class="col-12">
        <!--top buttons area-->
        <div class="mb-2">
            <a href="<?= SYS_URL ?>technicians/add.php" class="btn btn-outline-dark <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-plus"></i> New Technician</a>
        </div> <!--top buttons area end-->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Active Technicians</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <?php
                $sql = "SELECT u.UserId, u.FirstName, u.LastName, u.MobileNo, d.Name, t.SkilledCategory, ic.category_name,t.EmpId, t.SkillLevel,s.Status "
                        . "FROM technicians t INNER JOIN employees e ON e.EmployeeId = t.EmpId "
                        . "INNER JOIN users u ON u.UserId = e.UserId "
                        . "INNER JOIN districts d ON d.Id = u.DistrictId "
                        . "INNER JOIN item_category ic ON ic.id = t.SkilledCategory "
                        . "INNER JOIN status s ON s.StatusId=u.Status";
                $result = $db->query($sql);
                ?>
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Technician</th>
                            <th>Mobile No.</th>
                            <th>District</th>
                            <th>Status</th>
                            <th>Skilled Category</th>
                            <th>Skill Level</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?= $row['FirstName'] ?> <?= $row['LastName'] ?></td>
                                    <td><?= $row['MobileNo'] ?></td>
                                    <td><?= $row['Name'] ?></td>
                                    <td>
                                        <span class="<?= $row['Status'] == 'Active' ? 'badge badge-success' : 'badge badge-danger' ?>" style="width:80%"><?= $row['Status'] ?></span>
                                    </td>
                                    <td><?= $row['category_name'] ?></td>
                                    <td>
                                        <div class="progress progress-xs">
                                            <div class="progress-bar 
                                            <?php
                                            // different progress bar colours according to category
                                            if ($row['SkilledCategory'] == 1) {
                                                echo 'bg-primary';
                                            } elseif ($row['SkilledCategory'] == 2) {
                                                echo 'bg-dark';
                                            }
                                            ?>
                                                 progress-bar-striped" role="progressbar"
                                                 aria-valuenow="<?= $row['SkillLevel'] ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $row['SkillLevel'] ?>%">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group">
                                            <a href="<?= SYS_URL ?>technicians/view_schedule.php?id=<?= $row['EmpId'] ?>" class="btn btn-primary <?= $privilege['Select'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-eye"></i> View Schedule</a>
                                            <a href="<?= SYS_URL ?>technicians/edit.php?userid=<?= $row['UserId'] ?>" class="btn btn-outline-dark <?= $privilege['Edit'] == '0' ? 'disabled' : '' ?>" ><i class="fas fa-edit"></i> Edit</a>
                                            <?php
                                            if ($row['Status'] == 'Active') {
                                                ?>
                                                <a href = "<?= SYS_URL ?>technicians/action.php?action=disable&userid=<?= $row['UserId'] ?>" onclick = "return confirmDisable();" class = "btn btn-outline-danger <?= $privilege['Delete'] == '0' ? 'disabled' : '' ?>"><i class = "fas fa-ban"></i></a>
                                                <?php
                                            } else {
                                                ?>
                                                <a href = "<?= SYS_URL ?>technicians/action.php?action=enable&userid=<?= $row['UserId'] ?>" onclick = "return confirmEnable();" class = "btn btn-outline-success <?= $privilege['Delete'] == '0' ? 'disabled' : '' ?>"><i class = "fas fa-check"></i></a>
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
    function confirmDelete() {
        return confirm("Are you sure you want to delete this employee?");
    }
</script>