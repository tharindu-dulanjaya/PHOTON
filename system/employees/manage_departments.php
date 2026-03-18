<?php
ob_start();
include_once '../init.php';

$link = "Employee Departments";
$breadcrumb_item = "Departments";
$breadcrumb_item_active = "Manage";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('1'); // 1 is the module id for Employee Management

$db = dbConn();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);
    // create new Department
    if ($action == 'add') {
        $new_department = dataClean($new_department);
        $message = array();
        if (empty($new_department)) {
            $message['new_department'] = "Please enter new Department name!";
        }else{
            // check if this Department already exists
            $sql = "SELECT * FROM departments WHERE Department='$new_department'";
            $result = $db->query($sql);
            if($result->num_rows>0){ // means already exists
                $message['new_department'] = "Department already exists!";
            }
        }
        if (empty($message)) {
            $sql = "INSERT INTO departments(Department) VALUES ('$new_department')";
            $db->query($sql);
        }
    }
    
    // edit existing Department
    if ($action == 'edit') {
        $change_department = dataClean($change_department);
        $message = array();
        if (empty($change_department)) {
            $message['change_department'] = "Please enter the updated Department name!";
        }else{
            // check if this Department already exists
            $sql = "SELECT * FROM departments WHERE Department='$change_department'";
            $result = $db->query($sql);
            if($result->num_rows>0){ // means already exists
                $message['change_department'] = "Department already exists!";
            }
        }
        if (empty($message)) {
            $sql = "UPDATE departments SET Department='$change_department' WHERE Id='$depart_id';";
            $db->query($sql);
        }
    }
}
?> 
<div class="row">
    <div class="col-8">
        <!--top buttons area-->
        <div class="mb-2">
            <a href="<?= SYS_URL ?>employees/manage.php" class="btn btn-outline-dark"><i class="fas fa-arrow-left "> </i> Back</a>
            <button type="button" class="btn btn-success" <?= $privilege['Add'] == '0' ? 'disabled' : '' ?> data-bs-toggle="modal" data-bs-target="#createDepart">
                <i class="fas fa-plus"></i>  New Department
            </button>                                
        </div> <!--top buttons area end-->

        <!-- Create Department Modal -->
        <div class="modal fade" id="createDepart" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title fs-5" id="createDepartLabel">Add New Department</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                        <div class="modal-body">
                            <label for="new_department" class="col-form-label">New Department :</label>
                            <input type="text" class="form-control" name="new_department" id="new_department">
                            <span class="error_span text-danger"><?= @$message['new_department'] ?></span><br>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" name="action" value="add">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-info">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!--Update Department Modal--> 
        <div class="modal fade" id="updateDepart" tabindex="-1" aria-labelledby="updateDepartLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title fs-5" id="updateDepartLabel">Update Department Name</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                        <div class="modal-body">
                            <label for="change_department" class="form-label">Department Name</label>
                            <input type="text" class="form-control" id="change_department" name="change_department">
                            <span class="error_span text-danger"><?= @$message['change_department'] ?></span><br>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" id="depart_id" name="depart_id">
                            <input type="hidden" name="action" value="edit">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Employee Departments</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <?php
                $sql = "SELECT * FROM departments";
                $result = $db->query($sql);
                ?>
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Department</th>
                            <th></th>                          
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $id = $row['Id'];
                                $department = $row['Department'];
                                ?>
                                <tr>
                                    <td><?= $id ?></td>
                                    <td><?= $department ?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary" <?= $privilege['Edit'] == '0' ? 'disabled' : '' ?> data-bs-toggle="modal" data-bs-target="#updateDepart" data-departid="<?= $id ?>" data-department="<?= $department ?>">Edit</button>
                                    </td>
                                </tr>

                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="3">No records found.</td>
                            </tr>
                            <?php
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
    // use jQuery to pass data to the modal when button is clicked
    $(document).ready(function () {
        $('#updateDepart').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var department_id = button.data('departid');
            var department = button.data('department');

            var modal = $(this);
            modal.find('.modal-body input#change_department').val(department);
            modal.find('.modal-footer input#depart_id').val(department_id);
        });
    });

    function confirmDelete() {
        return confirm("Are you sure you want to delete this employee?");
    }
</script>