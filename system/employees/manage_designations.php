<?php
ob_start();
include_once '../init.php';

$link = "Employee Designations";
$breadcrumb_item = "Designations";
$breadcrumb_item_active = "Manage";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('1'); // 1 is the module id for Employee Management

$db = dbConn();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);
    // create new designation
    if ($action == 'add') {
        $new_designation = dataClean($new_designation);
        $message = array();
        if (empty($new_designation)) {
            $message['new_designation'] = "Please enter the new Designation!";
        }else{
            // check if this designation already exists
            $sql = "SELECT * FROM designations WHERE Designation='$new_designation'";
            $result = $db->query($sql);
            if($result->num_rows>0){ // means already exists
                $message['new_designation'] = "Designation already exists!";
            }
        }
        if (empty($message)) {
            $sql = "INSERT INTO designations(Designation) VALUES ('$new_designation')";
            $db->query($sql);
        }
    }

    // edit existing designation
    if ($action == 'edit') {
        $change_designation = dataClean($change_designation);
        $message = array();
        if (empty($change_designation)) {
            $message['change_designation'] = "Please enter the updated Designation!";
        }else{
            // check if this designation already exists
            $sql = "SELECT * FROM designations WHERE Designation='$change_designation'";
            $result = $db->query($sql);
            if($result->num_rows>0){ // means already exists
                $message['change_designation'] = "Designation already exists!";
            }
        }
        if (empty($message)) {
            $sql = "UPDATE designations SET Designation='$change_designation' WHERE Id='$desig_id';";
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
            <button type="button" class="btn btn-info" <?= $privilege['Add'] == '0' ? 'disabled' : '' ?> data-bs-toggle="modal" data-bs-target="#createDesig">
                <i class="fas fa-plus"></i>  New Designation
            </button>                                
        </div> <!--top buttons area end-->

        <!-- Create Designation Modal -->
        <div class="modal fade" id="createDesig" tabindex="-1" aria-labelledby="createDesigLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title fs-5" id="createDesigLabel">Create New Designation</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                        <div class="modal-body">
                            <label for="new_designation" class="col-form-label">New Designation :</label>
                            <input type="text" class="form-control" name="new_designation" id="new_designation">
                            <span class="error_span text-danger"><?= @$message['new_designation'] ?></span><br>
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

        <!--Update Designation Modal--> 
        <div class="modal fade" id="updateDesig" tabindex="-1" aria-labelledby="updateDesigLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title fs-5" id="updateDesigLabel">Update Designation</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                        <div class="modal-body">
                            <label for="change_designation" class="form-label">Designation</label>
                            <input type="text" class="form-control" id="change_designation" name="change_designation">
                            <span class="error_span text-danger"><?= @$message['change_designation'] ?></span><br>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" id="desig_id" name="desig_id">
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
                <h3 class="card-title">Employee Designations</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <?php
                $sql = "SELECT * FROM designations";
                $result = $db->query($sql);
                ?>
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Designation</th>
                            <th></th>                          
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $id = $row['Id'];
                                $designation = $row['Designation'];
                                ?>
                                <tr>
                                    <td><?= $id ?></td>
                                    <td><?= $designation ?></td>
                                    <td>
                                        <?php
                                        // admin can not be edited. so, edit button is hidden for admin
                                        // Id = 1 is the admin
                                        if ($row['Id'] != 1) {
                                            ?>
                                            <button type="button" class="btn btn-primary" <?= $privilege['Edit'] == '0' ? 'disabled' : '' ?> data-bs-toggle="modal" data-bs-target="#updateDesig" data-desigid="<?= $id ?>" data-designation="<?= $designation ?>">Edit</button>
                                            <?php
                                        }
                                        ?>                                        
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
        $('#updateDesig').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var designation_id = button.data('desigid');
            var designation = button.data('designation');

            var modal = $(this);
            modal.find('.modal-body input#change_designation').val(designation);
            modal.find('.modal-footer input#desig_id').val(designation_id);
        });
    });

</script>