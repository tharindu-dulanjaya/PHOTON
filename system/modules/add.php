<?php
ob_start();
include_once '../init.php';

$db = dbConn();

$link = "New Module";
$breadcrumb_item = "Modules";
$breadcrumb_item_active = "Add";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('8'); // 8 is the module id for Module Management

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);
    $module_name = dataClean($module_name);
    $path = dataClean($path);
    $file = dataClean($file);
    
    $message = array();
    
    if (empty($module_name)) {
        $message['module_name'] = "Please enter the Module Name!";
    } else {
        if (ctype_alpha(str_replace(' ', '', $module_name)) === false) {
            $message['module_name'] = "Only letters and white spaces are allowed";
        }else{
            // check if the module name already exists
            $sql = "SELECT * FROM modules WHERE Name='$module_name'";
            $result = $db->query($sql);
            if($result->num_rows>0){ // means already exists
                $message['module_name'] = "Module Name already exists!";
            }
        }
    }
    if (empty($path)) {
        $message['path'] = "Path name cannot be empty!";
    }
    if (empty($file)) {
        $message['file'] = "File name cannot be empty!";
    }
    if (empty($icon)) {
        $message['icon'] = "Please enter icon name!";
    }
    if (empty($index)) {
        $message['index'] = "Please define the index value!";
    }
    if (empty($status)) {
        $message['status'] = "Please select the status!";
    }
    
    if (empty($message)) {
        $sql = "INSERT INTO modules(Name,Path,File,Icon,Idx,Status) VALUES ('$module_name','$path','$file','$icon','$index','$status')";
        $db->query($sql);

        header("Location:manage.php");
    }
}
?>

<div class="row">
    <div class="col-12">

        <a href="manage.php" class="btn btn-dark mb-2"><i class="fas fa-users"></i> View All</a>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Add New Module</h3>
            </div>

            <!--Add novalidate attribute to skip browser validations-->
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="supplier_name">Module Name</label>
                            <input type="text" class="form-control" id="module_name" name="module_name" placeholder="Customer Management" value="<?= @$module_name ?>">
                            <span class="error_span text-danger"><?= @$message['module_name'] ?></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="path">Path</label>
                            <input type="text" class="form-control" name="path" id="path" placeholder="customers" value="<?= @$path ?>" required>
                            <span class="error_span text-danger"><?= @$message['path'] ?></span><br>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="file">File Name</label>
                            <input type="text" class="form-control border border-1 border-dark-subtle" name="file" id="file" placeholder="manage" value="<?= @$file ?>" required>
                            <span class="error_span text-danger"><?= @$message['file'] ?></span><br>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="icon">Icon</label>
                            <input type="text" class="form-control" name="icon" id="icon" placeholder="fas fa-user" value="<?= @$icon ?>" required>
                            <span class="error_span text-danger"><?= @$message['icon'] ?></span><br>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="index">Index</label>
                            <input type="text" class="form-control" name="index" id="index" placeholder="Position in the menu" value="<?= @$index ?>" required>
                            <span class="error_span text-danger"><?= @$message['index'] ?></span><br>
                        </div>
                        <div class="form-group col-md-4">
                            <?php
                            $db = dbConn();
                            $sql = "SELECT * FROM status";
                            $result = $db->query($sql);
                            ?>
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="" disabled="disabled">Active / Inactive</option>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <option value="<?= $row['StatusId'] ?>" <?= @$status == $row['StatusId'] ? 'selected' : '' ?>> <?= $row['Status'] ?> </option>
                                    <?php
                                }
                                ?>
                            </select>
                            <span class="error_span text-danger"><?= @$message['status'] ?></span><br>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary" <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>>Add Module</button>
                </div>
            </form>

        </div>
    </div>
</div>


<?php
$content = ob_get_clean();
include '../layouts.php';
?>