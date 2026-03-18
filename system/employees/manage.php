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
$link = "Employee Management";
$breadcrumb_item = "Employees";
$breadcrumb_item_active = "Manage";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('1'); // 1 is the module id for Employee Management
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
            <a href="<?= SYS_URL ?>employees/add.php" class="btn btn-outline-dark <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>" ><i class="fas fa-plus"></i> New Employee</a>
            <a href="<?= SYS_URL ?>employees/manage_designations.php" class="btn btn-outline-info"> Manage Designations</a>
            <a href="<?= SYS_URL ?>employees/manage_departments.php" class="btn btn-outline-success"> Manage Departments</a>
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
                                <div class="form-group col-md-3">
                                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Filter By First Name" value="<?= @$_POST['first_name'] ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Filter By Last Name" value="<?= @$_POST['last_name'] ?>">
                                </div>                                
                                <div class="form-group col-md-3">
                                    <?php
                                    $sql = "SELECT * FROM designations";
                                    $result = $db->query($sql);
                                    ?>
                                    <select class="form-control" name="designation">
                                        <option value="" readonly>Filter By Designation</option>
                                        <?php while ($row = $result->fetch_assoc()) { ?>
                                            <option value="<?= $row['Id'] ?>" <?= @$_POST['designation'] == $row['Id'] ? 'selected' : '' ?>><?= $row['Designation'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <?php
                                    $sql = "SELECT * FROM departments";
                                    $result = $db->query($sql);
                                    ?>
                                    <select class="form-control" name="department">
                                        <option value="" readonly>Filter By Department</option>
                                        <?php while ($row = $result->fetch_assoc()) { ?>
                                            <option value="<?= $row['Id'] ?>" <?= @$_POST['department'] == $row['Id'] ? 'selected' : '' ?>><?= $row['Department'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-dark float-right"><i class="fas fa-search"></i> Filter</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!--Filter end-->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Employee Details</h3>
            </div>
            <div class="card-body table-responsive p-2">
                <?php
                //this where part executes with the filter button clicked(POST)
                $where = null;
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    extract($_POST); // to get data from form fields
                    // if someone filter from date, it will be submitted by the form using POST method. then the sql query will be updated with WHERE clause
                    if (!empty($from_date) && !empty($to_date)) {
                        $where .= " e.AppointmentDate BETWEEN '$from_date' AND '$to_date' AND";
                    }
                    if (!empty($first_name)) {
                        $where .= " u.FirstName LIKE '%$first_name%' AND";
                    }
                    if (!empty($last_name)) {
                        $where .= " u.LastName LIKE '%$last_name%' AND";
                    }
                    if (!empty($designation)) {
                        $where .= " e.DesignationId = '$designation' AND";
                    }
                    if (!empty($department)) {
                        $where .= " e.DepartmentId = '$department' AND";
                    }
                    if (!empty($where)) {
                        $where = substr($where, 0, -3); // removes the last 3 characters (AND part)
                        $where = " WHERE $where"; // final complete WHERE statement
                    }
                } else {
                    // must required where part. this executes when the page loads without post(not filtered)
                    $where = null;
                }
                $sql = "SELECT u.UserId,t.Title,u.FirstName,u.LastName,e.AppointmentDate,e.DesignationId,p.Designation,e.DepartmentId,d.Department,s.Status "
                        . "FROM users u "
                        . "INNER JOIN employees e ON e.UserId = u.UserId "
                        . "INNER JOIN departments d ON d.Id = e.DepartmentId "
                        . "INNER JOIN designations p ON p.Id = e.DesignationId "
                        . "INNER JOIN titles t ON u.TitleId = t.Id "
                        . "INNER JOIN status s ON s.StatusId=u.Status "
                        . "$where "
                        . "ORDER BY e.DesignationId";
                $result = $db->query($sql);
                ?>
                <table id="employeeTable" class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Appointment Date</th>
                            <th>Designation</th>
                            <th>Department</th>
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
                                    <td><?= $row['Title'] ?> <?= $row['FirstName'] ?> <?= $row['LastName'] ?></td>
                                    <td><?= $row['AppointmentDate'] ?></td>
                                    <td><?= $row['Designation'] ?></td>
                                    <td><?= $row['Department'] ?></td>
                                    <td>
                                        <span class="<?= $row['Status'] == 'Active' ? 'badge badge-success' : 'badge badge-danger' ?>" style="width:80%"><?= $row['Status'] ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group">
                                            <a href="<?= SYS_URL ?>employees/privileges.php?userid=<?= $row['UserId'] ?>" class="btn btn-info"><i class="fas fa-universal-access"></i> Privileges</a>
                                            <a href="<?= SYS_URL ?>employees/edit.php?userid=<?= $row['UserId'] ?>" class="btn btn-outline-dark <?= $privilege['Edit'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-edit"></i> Edit</a>
                                            <?php
                                            // admin can not be deleted. so, delete button is hidden
                                            // DesignationId = 1 is the admin
                                            if ($row['DesignationId'] != 1) {
                                                if ($row['Status'] == 'Active') {
                                                    ?>
                                                    <a href = "<?= SYS_URL ?>employees/action.php?action=disable&userid=<?= $row['UserId'] ?>" onclick = "return confirmDisable();" class = "btn btn-outline-danger <?= $privilege['Delete'] == '0' ? 'disabled' : '' ?>"><i class = "fas fa-ban"></i></a>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <a href = "<?= SYS_URL ?>employees/action.php?action=enable&userid=<?= $row['UserId'] ?>" onclick = "return confirmEnable();" class = "btn btn-outline-success <?= $privilege['Delete'] == '0' ? 'disabled' : '' ?>"><i class = "fas fa-check"></i></a>
                                                    <?php
                                                }
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
    $(function () {
        // method 1
        $("#employeeTable2").DataTable({
            "responsive": true,
            "lengthChange": false,
            "searching": false,
            "paging": false,
            "info": true,
            "autoWidth": false,
            "buttons": ["pdf", "print"]
        }).buttons().container().appendTo('#employeeTable2_wrapper .col-md-6:eq(0)');

        // method 2
        $('#employeeTable1').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true
        });
    });

    function confirmDisable() {
        return confirm("Are you sure to disable this employee account?");
    }
    function confirmEnable() {
        return confirm("Are you sure to enable this employee account?");
    }
</script>