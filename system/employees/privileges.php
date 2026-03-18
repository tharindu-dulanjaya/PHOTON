<?php
ob_start();
session_start(); // to check permission
include_once '../init.php';

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('1'); // 1 is the module id for Employee Management

$db = dbConn();
extract($_POST);
extract($_GET); //to get userid

$sql = "SELECT u.FirstName, u.LastName, d.Designation "
        . "FROM users u INNER JOIN employees e ON e.UserId = u.UserId "
        . "INNER JOIN designations d ON d.Id = e.DesignationId "
        . "WHERE u.UserId = '$userid'";
$result = $db->query($sql);
$row = $result->fetch_assoc();

$link = $row['FirstName'] . ' ' . $row['LastName'] . ' - ' . $row['Designation'];
$breadcrumb_item = "Employee";
$breadcrumb_item_active = "Privileges";

if ($_SERVER['REQUEST_METHOD'] == 'POST') { // update privileges form is submitted
    $message = array();

    if (empty($module)) {
        $message['module'] = "Please select the Module name";
    }
    if (empty($message)) {
        if (isset($_POST['privileges'])) {
            // find what checkboxes have been checked by user
            // check if 'Add' includes in the $privileges array, if so it will assign 1 to $add. otherwise 0
            $add = in_array('Add', $privileges) ? 1 : 0;
            $edit = in_array('Edit', $privileges) ? 1 : 0;
            $delete = in_array('Delete', $privileges) ? 1 : 0;
            $select = in_array('Select', $privileges) ? 1 : 0;

            // check if there is already an row for the selected module. then it should be an UPDATE
            $sql = "SELECT * FROM user_modules WHERE UserId = '$userid' AND ModuleId = '$module'";
            $result = $db->query($sql);
            if ($result->num_rows > 0) {
                // `` tilde marks used here for column names, bcz they are mysql keywords
                $sql = "UPDATE user_modules SET `Add`='$add', `Edit`='$edit', `Delete`='$delete', `Select`='$select' WHERE UserId='$userid' AND ModuleId='$module'";
                $db->query($sql);
            } else {
                // should INSERT a new record
                // `` tilde marks used here for column names, bcz they are mysql keywords
                $sql = "INSERT INTO user_modules (UserId, ModuleId, `Add`, `Edit`, `Delete`, `Select`) VALUES ('$userid','$module','$add','$edit','$delete','$select')";
                $db->query($sql);
            }
        } else { // privileges array is empty, means every CRUD action is denied 
            // check if there is already an row for the selected module. then it should be an UPDATE
            $sql = "SELECT * FROM user_modules WHERE UserId = '$userid' AND ModuleId = '$module'";
            $result = $db->query($sql);
            if ($result->num_rows > 0) {
                // `` tilde marks used here for column names, bcz they are mysql keywords
                $sql = "UPDATE user_modules SET `Add`='0', `Edit`='0', `Delete`='0', `Select`='0' WHERE UserId='$userid' AND ModuleId='$module'";
                $db->query($sql);
            } else {
                // should INSERT a new record
                // it is not practical to assign new module with no any CRUD privileges
            }
        }
    }
}
?> 
<a href="<?= SYS_URL ?>employees/manage.php" class="btn btn-outline-dark"><i class="fas fa-arrow-left "> </i> Go Back</a>

<div class="row mt-2">    
    <div class="col-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Existing Privileges</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <?php
                $sql = "SELECT * FROM user_modules u INNER JOIN modules m ON m.Id = u.ModuleId WHERE u.UserId = '$userid' AND m.Status = '1'";
                $result = $db->query($sql);
                if ($result->num_rows > 0) {
                    ?>
                    <table class="table table-hover text-nowrap" id="modules">
                        <thead>
                            <tr>
                                <th>Module Name</th>
                                <th>Add</th>
                                <th>Edit</th>
                                <th>Delete</th>
                                <th>View</th>  
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?= $row['Name'] ?></td>
                                    <td><?= $row['Add'] == 1 ? '<i class="fas fa-check"></i>' : '<i class="fas fa-ban"></i>' ?></td>
                                    <td><?= $row['Edit'] == 1 ? '<i class="fas fa-check"></i>' : '<i class="fas fa-ban"></i>' ?></td>
                                    <td><?= $row['Delete'] == 1 ? '<i class="fas fa-check"></i>' : '<i class="fas fa-ban"></i>' ?></td>
                                    <td><?= $row['Select'] == 1 ? '<i class="fas fa-check"></i>' : '<i class="fas fa-ban"></i>' ?></td> 
                                    <?php
                                    if ($userid == 7 && $row['ModuleId'] == 1) {
                                        // admins employee management privilege cannot be removed. then the system would break
                                    } else {
                                        ?>
                                        <td><a href="<?= SYS_URL ?>employees/remove_module_access.php?uid=<?= $userid ?>&mid=<?= $row['ModuleId'] ?>" onclick="return confirmDelete();" ><i id="removeBtn" class="fas fa-trash" > </i></a></td> 
                                        <?php
                                    }
                                    ?>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                        <p class="p-4">No any modules assigned</p>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-5">        
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title">Update Privileges</h3>
            </div>
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-10">
                            <?php
                            $sql = "SELECT * FROM modules WHERE Status='1'";
                            $result = $db->query($sql);
                            ?>
                            <label for="module">Select Module</label>
                            <!-- this.value pass the selected module id-->
                            <select name="module" id="module" class="form-control" onchange="loadPrivilegesByModule(this.value, <?= $userid ?>)">
                                <option value="">--</option>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <option value="<?= $row['Id'] ?>" <?= @$module == $row['Id'] ? 'selected' : '' ?>> <?= $row['Name'] ?> </option>
                                    <?php
                                }
                                ?>
                            </select>
                            <span class="error_span text-danger mt-4"><?= @$message['module'] ?></span><br>
                        </div>
                    </div>

                    <!-- will populate using ajax when a module is selected in the above dropdown-->
                    <div class="row" id="modulePrivileges">

                    </div>
                </div>
                <div class="card-footer">
                    <input type="hidden" name="userid" value="<?= $userid ?>">
                    <button type="submit" class="btn btn-warning">Update Privileges</button>
                </div>
            </form>
        </div>
        <div class="row mt-3">
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include '../layouts.php';
?>

<script>

    function loadPrivilegesByModule(moduleId, userId) {
        if (moduleId) {
            $.ajax({
                url: 'loadPrivilegesByModule.php?moduleId=' + moduleId + '&userId=' + userId,
                type: 'GET',
                success: function (data) {
                    $("#modulePrivileges").html(data);
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }
    }

    function confirmDelete() {
        return confirm("Are you sure to remove access to this module?");
    }
</script>

