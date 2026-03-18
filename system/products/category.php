<?php
ob_start();
include_once '../init.php';

$link = "Product Categories";
$breadcrumb_item = "Category";
$breadcrumb_item_active = "Manage";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('9'); // 9 is the module id for Product Management

$db = dbConn();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);
    // create new category
    if ($action == 'add') {
        $new_category = dataClean($new_category);
        $message = array();
        if (empty($new_category)) {
            $message['new_category'] = "Please enter the new Category!";
        } else {
            // check if this category already exists
            $sql = "SELECT * FROM item_category WHERE category_name='$new_category'";
            $result = $db->query($sql);
            if ($result->num_rows > 0) { // means already exists
                $message['new_category'] = "Category already exists!";
            }
        }
        if (empty($reorder_level)) {
            $message['reorder_level'] = "Please enter the reorder level!";
        }
        if (empty($message)) {
            $sql = "INSERT INTO item_category(category_name,status,minimum_reorder_level) VALUES ('$new_category','1','$reorder_level')";
            $db->query($sql);
        }
    }

    // edit existing category
    if ($action == 'edit') {
        $change_category = dataClean($change_category);
        $message = array();
        if (empty($change_category)) {
            $message['change_category'] = "Enter the updated Category name!";
        } else {
            // check if this category already exists
            $sql = "SELECT * FROM item_category WHERE category_name='$change_category' AND id <> '$cat_id'";
            $result = $db->query($sql);
            if ($result->num_rows > 0) { // means already exists
                $message['change_category'] = "Category already exists!";
            }
        }
        if (empty($edit_reorder_level)) {
            $message['edit_reorder_level'] = "Please enter the reorder level!";
        }
        if (empty($message)) {
            $sql = "UPDATE item_category SET category_name='$change_category', minimum_reorder_level='$edit_reorder_level' WHERE id='$cat_id'";
            $db->query($sql);
        }
    }
}
?> 
<div class="row">
    <div class="col-7">
        <!--top buttons area-->
        <div class="mb-2">   
            <a href="<?= SYS_URL ?>products/manage.php" class="btn btn-outline-dark"><i class="fas fa-arrow-left "> </i> Go Back</a>
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#createCategory" <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>>
                <i class="fas fa-plus"></i>  New Product Category
            </button>                                
        </div> <!--top buttons area end-->

        <!-- Create Category Modal -->
        <div class="modal fade" id="createCategory">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title fs-5">Create New Category</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                        <div class="modal-body">
                            <label for="new_category" class="col-form-label">New Category Name :</label>
                            <input type="text" class="form-control" name="new_category" id="new_category">
                            <span class="error_span text-danger"><?= @$message['new_category'] ?></span><br>
                            <label for="reorder_level" class="col-form-label">Minimum Reorder Level :</label>
                            <input type="number" class="form-control" min="1" name="reorder_level" id="reorder_level">
                            <span class="error_span text-danger"><?= @$message['reorder_level'] ?></span><br>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" name="action" value="add">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-info" <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>>Add Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!--Update Category Modal--> 
        <div class="modal fade" id="updateCategory">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title fs-5" id="updateDesigLabel">Update Category</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                        <div class="modal-body">
                            <label for="change_category" class="form-label">Category name</label>
                            <input type="text" class="form-control" id="change_category" name="change_category">
                            <span class="error_span text-danger"><?= @$message['change_category'] ?></span><br>
                            <label for="edit_reorder_level" class="col-form-label">Minimum Reorder Level :</label>
                            <input type="number" class="form-control" min="1" name="edit_reorder_level" id="edit_reorder_level">
                            <span class="error_span text-danger"><?= @$message['edit_reorder_level'] ?></span><br>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" id="cat_id" name="cat_id">
                            <input type="hidden" name="action" value="edit">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" <?= $privilege['Edit'] == '0' ? 'disabled' : '' ?>>Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Product Categories</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <?php
                $sql = "SELECT * FROM item_category INNER JOIN status ON status.StatusId=item_category.status";
                $result = $db->query($sql);
                ?>
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Reorder Level</th>                            
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $id = $row['id'];
                                $category = $row['category_name'];
                                $level = $row['minimum_reorder_level'];
                                ?>
                                <tr>
                                    <td><?= $id ?></td>
                                    <td><?= $category ?></td>                                    
                                    <td>
                                        <span class="<?= $row['Status'] == 'Active' ? 'badge badge-success' : 'badge badge-danger' ?>" style="width:80%"><?= $row['Status'] ?></span>
                                    </td>
                                    <td><?= $level ?></td>
                                    <td>
                                        <div class="btn-group btn-group">
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateCategory" <?= $privilege['Add'] == '0' ? 'disabled' : '' ?> data-catid="<?= $id ?>" data-category="<?= $category ?>" data-level="<?= $level ?>"><i class="fas fa-edit"></i> Edit</button>
                                            <?php
                                            if ($row['Status'] == 'Active') {
                                                    ?>
                                                    <a href = "<?= SYS_URL ?>products/category_action.php?action=disable&catid=<?= $id ?>" onclick = "return confirmDisable();" class = "btn btn-outline-danger <?= $privilege['Delete'] == '0' ? 'disabled' : '' ?>"><i class = "fas fa-ban"></i></a>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <a href = "<?= SYS_URL ?>products/category_action.php?action=enable&catid=<?= $id ?>" onclick = "return confirmEnable();" class = "btn btn-outline-success <?= $privilege['Delete'] == '0' ? 'disabled' : '' ?>"><i class = "fas fa-check"></i></a>
                                                    <?php
                                                }
                                            ?>
                                        </div>
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
        $('#updateCategory').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var category_id = button.data('catid');
            var category = button.data('category');
            var level = button.data('level');

            var modal = $(this);
            modal.find('.modal-body input#change_category').val(category);
            modal.find('.modal-body input#edit_reorder_level').val(level);
            modal.find('.modal-footer input#cat_id').val(category_id);
        });
    });

</script>