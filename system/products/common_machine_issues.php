<?php
ob_start();
include_once '../init.php';

$db = dbConn();
extract($_POST);

$link = "Common Machine Issues";
$breadcrumb_item = "Machine";
$breadcrumb_item_active = "Issues";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('9'); // 9 is the module id for Product Management

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'add_issue') {
    $common_issue = dataClean($common_issue);
    $message = array();

    if (empty($machine_cat)) {
        $message['machine_cat'] = "Please select the machine category!";
    }
    if (empty($common_issue)) {
        $message['common_issue'] = "Enter the common issue";
    } else {
        // check if the issue already exists in this category. it cannot be allowed
        $sql = "SELECT * FROM common_machine_issues WHERE MachineCategory='$machine_cat' AND Issue='$common_issue'";
        $result = $db->query($sql);
        if ($result->num_rows > 0) {
            $message['common_issue'] = "This issue already exists!";
        }
    }
    if (empty($required_techs)) {
        $message['required_techs'] = "Please enter required Technician amount!";
    }

    if (empty($message)) {
        $sql = "INSERT INTO common_machine_issues(MachineCategory, Issue, ReqTechnicians) VALUES ('$machine_cat','$common_issue','$required_techs')";
        $db->query($sql);

        header("Location:common_machine_issues.php");
    }
}
?>

<div class="row">
    <div class="col-6">
        <a href="manage.php" class="btn btn-dark mb-2"> <i class="fas fa-arrow-left"></i> Go Back</a>
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Add New Issue</h3>
            </div>
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <?php
                            $sql = "SELECT * FROM item_category WHERE status='1'";
                            $result = $db->query($sql);
                            ?>
                            <label for="machine_cat">Machine Category</label>
                            <select name="machine_cat" id="machine_cat" class="form-control border border-1 border-dark-subtle">
                                <option value="">--</option>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <option value="<?= $row['id'] ?>" <?= @$machine_cat == $row['id'] ? 'selected' : '' ?>> <?= $row['category_name'] ?> </option>
                                    <?php
                                }
                                ?>
                            </select>                                    
                            <span class="error_span text-danger"><?= @$message['machine_cat'] ?></span>
                        </div>                        
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="common_issue">Common Issue</label>
                            <input type="text" class="form-control" id="common_issue" name="common_issue" placeholder="Enter common issue" value="<?= @$common_issue ?>">
                            <span class="error_span text-danger"><?= @$message['common_issue'] ?></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="required_techs">Required Technicians</label>
                            <input type="number" class="form-control" id="required_techs" name="required_techs" placeholder="No. of Technicians required to resolve the issue" value="<?= @$required_techs ?>">
                            <span class="error_span text-danger"><?= @$message['required_techs'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <input type="hidden" name="action" value="add_issue">
                    <button type="submit" class="btn btn-primary" <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>>Add Issue</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-6">
        <!--category filter-->
        <?php
        $sql = "SELECT * FROM item_category WHERE status='1'";
        $result = $db->query($sql);
        ?>
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
            <div class="row">
                <div class="col-7">
                    <div class="form-group">
                        <select name="cat" id="cat" class="form-control border border-1 border-dark-subtle">
                            <option value="">Filter by Machine Category</option>
                            <?php
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <option value="<?= $row['id'] ?>" <?= @$_POST['cat'] == $row['id'] ? 'selected' : '' ?>> <?= $row['category_name'] ?> </option>
                                <?php
                            }
                            ?>
                        </select>
                    </div> 
                </div>
                <div class="col-3">
                    <input type="hidden" name="action" value="filter">
                    <button type="submit" class="btn btn-dark">Load Issues</button>
                </div>
            </div>
        </form>

        <div class="card card">
            <div class="card-body table-responsive">
                <?php
                // $cat is created when the filter button is clicked
                if (!empty($cat)) { // if a category is selected
                    $sql2 = "SELECT * FROM common_machine_issues WHERE MachineCategory='$cat'";
                } else { // no category selected. display all issues 
                    $sql2 = "SELECT * FROM common_machine_issues";
                }
                $result2 = $db->query($sql2);
                if ($result->num_rows > 0) {
                    ?>
                    <table id="commonIssueTable" class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Issue</th>
                                <th>Required Technicians</th>                            
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row2 = $result2->fetch_assoc()) {
                                ?>
                                <tr >
                                    <td><?= $row2['Issue'] ?></td>
                                    <td><?= $row2['ReqTechnicians'] ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo 'No records found!';
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
    $(document).ready(function () {
        // data table
        $(function () {
            $("#commonIssueTable").DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": false,
                "ordering": false,
                "info": true,
                "autoWidth": false,
                "responsive": true
            });
        });
    });
</script>