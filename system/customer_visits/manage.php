<?php
ob_start();
session_start(); // to check permission
include_once '../init.php';

$db = dbConn();
$link = "Customer Visits";
$breadcrumb_item = "All Visits";
$breadcrumb_item_active = "Manage";

// check permission of the user
$current_url = $_SERVER['REQUEST_URI'];
if (!checkPermission($current_url, $_SESSION['USERID'])) {
    header("Location:../unauthorized.php");
}
// check the CRUD privileges for the logged in user
$privilege = checkprivilege('14'); // 14 is the module id for Customer Visits
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
            <a href="<?= SYS_URL ?>customer_visits/add.php" class="btn btn-info <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-user-plus"></i> New Visit</a>
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
                                    <input type="text" class="form-control" id="mill_name" name="mill_name" placeholder="Filter By Mill/Company" value="<?= @$_POST['mill_name'] ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <?php
                                    $sql = "SELECT * FROM districts";
                                    $result = $db->query($sql);
                                    ?>
                                    <select class="form-control" name="district">
                                        <option value="" readonly>Filter By District</option>
                                        <?php while ($row = $result->fetch_assoc()) { ?>
                                            <option value="<?= $row['Id'] ?>" <?= @$_POST['district'] == $row['Id'] ? 'selected' : '' ?>><?= $row['Name'] ?></option>
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
                <h3 class="card-title">Customer Details</h3>
                <div class="card-tools">                    
                </div>
            </div>
            <div class="card-body table-responsive p-2">
                <?php
                //this where part executes with the filter button clicked(POST)
                $where = null;
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    extract($_POST); // to get data from form fields
                    // if someone filter from date, it will be submitted by the form using POST method. then the sql query will be updated with WHERE clause
                    if (!empty($from_date) && !empty($to_date)) {
                        $where .= " c.RegisteredDate BETWEEN '$from_date' AND '$to_date' AND";
                    }
                    if (!empty($first_name)) {
                        $where .= " u.FirstName LIKE '%$first_name%' AND";
                    }
                    if (!empty($last_name)) {
                        $where .= " u.LastName LIKE '%$last_name%' AND";
                    }
                    if (!empty($mill_name)) {
                        $where .= " c.MillName LIKE '%$mill_name%' AND";
                    }
                    if (!empty($district)) {
                        $where .= " u.DistrictId = '$district' AND";
                    }
                    if (!empty($where)) {
                        $where = substr($where, 0, -3); // removes the last 3 characters (AND part)
                        $where = " WHERE $where"; // final complete WHERE statement
                    }
                } else {
                    // must required where part. this executes when the page loads without post(not filtered)
                    $where = null;
                }
                $sql = "SELECT * FROM users u "
                        . "INNER JOIN customers c ON c.UserId = u.UserId "
                        . "INNER JOIN titles t ON u.TitleId = t.Id "
                        . "INNER JOIN districts d ON u.DistrictId = d.Id "
                        . "INNER JOIN status s ON s.StatusId=u.Status "
                        . "$where ";
                $result = $db->query($sql);
                ?>
                <table id="customerTable" class="table table-hover text-nowrap">
                    <thead>
                        <tr>                            
                            <th>Name</th>
                            <th>Registered Date</th>
                            <th>Email Address</th>
                            <th>Mobile Number</th>
                            <th>Mill / Company</th>
                            <th>District</th>
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
                                    <td><?= $row['RegisteredDate'] ?></td>
                                    <td><?= $row['Email'] ?></td>
                                    <td><?= $row['MobileNo'] ?></td>
                                    <td><?= $row['MillName'] ?></td>
                                    <td><?= $row['Name'] ?></td>
                                    <td>
                                        <span class="<?= $row['Status'] == 'Active' ? 'badge badge-success' : 'badge badge-danger' ?>" style="width:100%"><?= $row['Status'] ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group">
                                            <!--<a href="<?= SYS_URL ?>customers/view.php?userid=<?= $row['UserId'] ?>" class="btn btn-primary <?= $privilege['Select'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-eye"></i>&nbsp; View</a>-->
                                            <a href="<?= SYS_URL ?>customers/edit.php?userid=<?= $row['UserId'] ?>" class="btn btn-outline-dark <?= $privilege['Edit'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-edit"></i>&nbsp; Edit</a>
                                            <?php
                                            if ($row['Status'] == 'Active') {
                                                ?>
                                                <a href = "<?= SYS_URL ?>customers/action.php?action=disable&userid=<?= $row['UserId'] ?>" onclick = "return confirmDisable();" class = "btn btn-outline-danger <?= $privilege['Delete'] == '0' ? 'disabled' : '' ?>"><i class = "fas fa-ban"></i></a>
                                                <?php
                                            } else {
                                                ?>
                                                <a href = "<?= SYS_URL ?>customers/action.php?action=enable&userid=<?= $row['UserId'] ?>" onclick = "return confirmEnable();" class = "btn btn-outline-success <?= $privilege['Delete'] == '0' ? 'disabled' : '' ?>"><i class = "fas fa-check"></i></a>
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
    $(function () {
        $("#customerTable1").DataTable({
            "responsive": true,
            "searching": false,
            "ordering": true,
            "info": true,
            "lengthChange": false,
            "autoWidth": false,
            "buttons": ["pdf"]
        }).buttons().container().appendTo('#customerTable1_wrapper .col-md-6:eq(0)');
        $('#cusTable').DataTable({
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
        return confirm("Are you sure to disable this customer account?");
    }
    function confirmEnable() {
        return confirm("Are you sure to enable this customer account?");
    }
</script>