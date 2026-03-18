<?php
include '../../function.php';
$db = dbConn();
extract($_GET);

// this data is filled in privileges page using ajax

$sql = "SELECT `Add`,`Edit`,`Delete`,`Select` FROM user_modules WHERE UserId = '$userId' AND ModuleId = '$moduleId'";
$result = $db->query($sql);

if ($result->num_rows > 0) { // already a record exists
    while ($row = $result->fetch_assoc()) {
        ?>
        <div class="form-group col-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="privileges[]" id="add" value="Add" <?= ($row['Add']) == 1 ? 'checked' : '' ?>>
                <label class="form-check-label" for="add">
                    Add
                </label>
            </div>
        </div>
        <div class="form-group col-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="privileges[]" id="edit" value="Edit" <?= ($row['Edit']) == 1 ? 'checked' : '' ?>>
                <label class="form-check-label" for="edit">
                    Edit
                </label>
            </div>
        </div>
        <div class="form-group col-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="privileges[]" id="delete" value="Delete" <?= ($row['Delete']) == 1 ? 'checked' : '' ?>>
                <label class="form-check-label" for="delete">
                    Delete
                </label>
            </div>
        </div>
        <div class="form-group col-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="privileges[]" id="select" value="Select" <?= ($row['Select']) == 1 ? 'checked' : '' ?>>
                <label class="form-check-label" for="select">
                    View
                </label>
            </div>
        </div>
        <?php
    }
} else {
    ?>
    <!--assigning a new module with empty check boxes-->
    <div class="form-group col-2">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="privileges[]" id="add" value="Add">
            <label class="form-check-label" for="add">
                Add
            </label>
        </div>
    </div>
    <div class="form-group col-2">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="privileges[]" id="edit" value="Edit">
            <label class="form-check-label" for="edit">
                Edit
            </label>
        </div>
    </div>
    <div class="form-group col-2">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="privileges[]" id="delete" value="Delete">
            <label class="form-check-label" for="delete">
                Delete
            </label>
        </div>
    </div>
    <div class="form-group col-2">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="privileges[]" id="select" value="Select">
            <label class="form-check-label" for="select">
                View
            </label>
        </div>
    </div>
    <?php
}

