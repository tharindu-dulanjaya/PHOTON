<?php
ob_start();
$db = dbConn();

$link = "Admin Dashboard";
$breadcrumb_item = "Dashboard";
$breadcrumb_item_active = "View";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('1'); // 1 is the module id for Employee Management
?> 
<div class="row">
    <div class="col-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Employee Details</h3>
            </div>
            <div class="card-body table-responsive p-2">
                <?php
                $sql = "SELECT * FROM users u "
                        . "INNER JOIN employees e ON e.UserId = u.UserId "
                        . "LEFT JOIN departments d ON d.Id = e.DepartmentId "
                        . "LEFT JOIN designations p ON p.Id = e.DesignationId "
                        . "LEFT JOIN titles t ON u.TitleId = t.Id";

                $result = $db->query($sql);
                ?>
                <table id="employeeTable" class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Designation</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?= $row['Title'] ?> <?= $row['FirstName'] ?> <?= $row['LastName'] ?></td>
                                    <td><?= $row['Designation'] ?></td>
                                    <td>
                                        <div class="btn-group btn-group">
                                            <a href="<?= SYS_URL ?>employees/privileges.php?userid=<?= $row['UserId'] ?>" class="btn btn-info <?= $privilege['Select'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-universal-access"></i> Privileges</a>
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
    <div class="col-4">

    </div>
</div>
<?php
$content = ob_get_clean();
include 'layouts.php';
?>