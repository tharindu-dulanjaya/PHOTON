<?php
ob_start();
include_once '../init.php';

$db = dbConn();

$link = "Product Management";
$breadcrumb_item = "Products";
$breadcrumb_item_active = "Add";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('9'); // 9 is the module id for Product Management

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);

    $model_no = dataClean($model_no);
    $product_name = dataClean($product_name);
    $description = dataClean($description);

    $message = array();

    if (empty($machine_cat)) {
        $message['machine_cat'] = "Please select the machine category!";
    } else {
        if ($machine_cat == 1) { // if category is color sorter
            if (empty($capacity)) {
                $message['capacity'] = "Capacity cannot be empty!";
            }
            if (empty($channels)) {
                $message['channels'] = "Please enter no. of Channels!";
            }
            if (empty($compressor)) {
                $message['compressor'] = "Please enter compressor capacity!";
            }
            if (empty($cameras)) {
                $message['cameras'] = "Please enter no. of Cameras!";
            }
            if (empty($power)) {
                $message['power'] = "Power cannot be empty!";
            }
            if (empty($voltage)) {
                $message['voltage'] = "Please enter Voltage!";
            }
            if (empty($ejectors)) {
                $message['ejectors'] = "Please enter no. of ejectors!";
            }
            if (empty($weight)) {
                $message['weight'] = "Please enter weight!";
            }
            if (empty($dimensions)) {
                $message['dimensions'] = "Please enter the dimensions!";
            }
        }
    }
    if (empty($model_no)) {
        $message['model_no'] = "Model Number cannot be empty!";
    } else {
        // check if there is any product with the same model. it cannot be allowed
        $sql = "SELECT model_no FROM items WHERE model_no='$model_no'";
        $result = $db->query($sql);
        if ($result->num_rows > 0) {
            $message['model_no'] = "This model number already exists!";
        }
    }
    if (empty($product_name)) {
        $message['product_name'] = "Please enter the Product name!";
    } else {
        // check if there is any product with the same name. it cannot be allowed
        $sql = "SELECT item_name FROM items WHERE item_name='$product_name'";
        $result = $db->query($sql);
        if ($result->num_rows > 0) {
            $message['product_name'] = "This product already exists!";
        }
    }
    if (empty($description)) {
        $message['description'] = "Please enter the product description!";
    }
    if (empty($status)) {
        $message['status'] = "Please select the status!";
    }

    // main product image upload
    if (!empty($_FILES['main_image']['name'])) {

        $file = $_FILES['main_image'];
        $location = "../../uploads/products";
        $uploadResult = uploadFile($file, $location);
        if ($uploadResult['upload']) {
            $main_image = $uploadResult['file'];
        } else {
            $error = $uploadResult['error_file'];
            $message['main_image'] = "<br>Image upload failed : $error";
        }
    } else {
        $message['main_image'] = "Please upload the product image!";
    }

    if (empty($message)) {
        $sql = "INSERT INTO items(item_name,model_no,item_description,item_category,item_image,status) VALUES ('$product_name','$model_no','$description','$machine_cat','$main_image','$status')";
        $db->query($sql);

        // get the last insert id of the product
        $product_id = $db->insert_id;

        // insert specs for color sorters only
        if ($machine_cat == 1) {
            $sql = "INSERT INTO item_specs(ItemId, Capacity, Channels, Compressor, Cameras, Power, Voltage, Ejectors, Weight, Dimensions) "
                    . "VALUES ('$product_id','$capacity','$channels','$compressor','$cameras','$power','$voltage','$ejectors','$weight','$dimensions')";
            $db->query($sql);
        }

        // upload extra images
        if (isset($_FILES['extra_images'])) {
            $extra_images = $_FILES['extra_images'];
            $location = '../../uploads/products';
            $uploadResult = uploadFiles($extra_images, $location); // assign the return value of the function. the '$messages' array variable values (multi dimensional array)
            foreach ($uploadResult as $key => $value) { // separates the values in the $messages array
                if ($value['upload']) {
                    $extra_images = $value['file'];
                    $sql = "INSERT INTO item_images(ItemId, ImagePath) VALUES ('$product_id','$extra_images')";
                    $db->query($sql);
                } else {
                    foreach ($value as $result) {
                        echo $result;
                    }
                }
            }
        }
        header("Location:manage.php");
    }
}
?>

<div class="row">
    <div class="col-12">

        <a href="manage.php" class="btn btn-dark mb-2"> <i class="fas fa-eye"></i> View All</a>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Add New Product</h3>
            </div>
            <!--Add novalidate attribute to skip browser validations-->
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data" novalidate>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-5">
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
                        <div class="form-group col-md-7">
                            <label for="model_no">Model Number</label>
                            <input type="text" class="form-control border border-1 border-dark-subtle" name="model_no" id="model_no" placeholder="Model Number" value="<?= @$model_no ?>" required>
                            <span class="error_span text-danger"><?= @$message['model_no'] ?></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="product_name">Product Name</label>
                            <input type="text" class="form-control" id="product_name" name="product_name" placeholder="Enter Product Name" value="<?= @$product_name ?>">
                            <span class="error_span text-danger"><?= @$message['product_name'] ?></span>
                        </div>
                    </div>
                    <div id="colorSorterSpecs" class="row" style="display: none;">
                        <div class="form-group col-md-3">
                            <label for="product_name">Capacity (kg/h)</label>
                            <input type="text" class="form-control" id="capacity" name="capacity" placeholder="500-1000" value="<?= @$capacity ?>">
                            <span class="error_span text-danger"><?= @$message['capacity'] ?></span>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="product_name">No. of Channels</label>
                            <input type="text" class="form-control" id="channels" name="channels" placeholder="No. of Channels" value="<?= @$channels ?>">
                            <span class="error_span text-danger"><?= @$message['channels'] ?></span>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="product_name">Compressor</label>
                            <input type="text" class="form-control" id="compressor" name="compressor" placeholder="3.5 KW Piston Compressor" value="<?= @$compressor ?>">
                            <span class="error_span text-danger"><?= @$message['compressor'] ?></span>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="product_name">No. of Cameras</label>
                            <input type="text" class="form-control" id="cameras" name="cameras" placeholder="No. of Cameras" value="<?= @$cameras ?>">
                            <span class="error_span text-danger"><?= @$message['cameras'] ?></span>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="product_name">Power (kW)</label>
                            <input type="text" class="form-control" id="power" name="power" placeholder="1.5 KW" value="<?= @$power ?>">
                            <span class="error_span text-danger"><?= @$message['power'] ?></span>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="product_name">Voltage</label>
                            <input type="text" class="form-control" id="voltage" name="voltage" placeholder="110-220V/ 50-60Hz" value="<?= @$voltage ?>">
                            <span class="error_span text-danger"><?= @$message['voltage'] ?></span>
                        </div>                        
                        <div class="form-group col-md-2">
                            <label for="product_name">No. of Ejectors</label>
                            <input type="text" class="form-control" id="ejectors" name="ejectors" placeholder="No. of Ejectors" value="<?= @$ejectors ?>">
                            <span class="error_span text-danger"><?= @$message['ejectors'] ?></span>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="product_name">Weight (kg)</label>
                            <input type="text" class="form-control" id="weight" name="weight" placeholder="650" value="<?= @$weight ?>">
                            <span class="error_span text-danger"><?= @$message['weight'] ?></span>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="product_name">Dimensions</label>
                            <input type="text" class="form-control" id="dimensions" name="dimensions" placeholder="780*450*1370mm" value="<?= @$dimensions ?>">
                            <span class="error_span text-danger"><?= @$message['dimensions'] ?></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="description">Product Description</label>
                            <textarea class="form-control" id="description" name="description" placeholder="Enter Product Description" style="height: 250px"  value="<?= @$description ?>"></textarea>
                            <span class="error_span text-danger"><?= @$message['description'] ?></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <h5>Select Main Product Image</h5>
                        <div class="input-group col-md-12">
                            <input type="file" class="form-control mb-2" id="main_image" name="main_image">                            
                        </div>
                        <span class="error_span text-danger"><?= @$message['main_image'] ?></span>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label>Additional Product Images (Optional)</label>
                            <div class="input-group">
                                <input type="file" class="form-control mb-2" id="extra_image1" name="extra_images[]">
                            </div>
                            <div class="input-group">
                                <input type="file" class="form-control mb-2" id="extra_image2" name="extra_images[]">
                            </div>
                            <div class="input-group">
                                <input type="file" class="form-control mb-2" id="extra_image3" name="extra_images[]">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?php
                            $sql = "SELECT * FROM status";
                            $result = $db->query($sql);
                            ?>
                            <label for="status">Product Status</label>
                            <select name="status" id="status" class="form-control border border-1 border-dark-subtle">
                                <option value="" disabled>--</option>
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
                    <button type="submit" class="btn btn-primary" <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>>Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php
$content = ob_get_clean();
include '../layouts.php';
?>

<script>
    $(document).ready(function () {
        // when the machine category changes, check it is a color sorter or not
        $('#machine_cat').change(function () {
            const selectedValue = $(this).val();
            if (selectedValue === '1') { //  Color Sorter is selected
                $('#colorSorterSpecs').show(); // show the specs div
            } else {
                $('#colorSorterSpecs').hide();
            }
        });

        // trigger change event on page load to set the initial state
        $('#machine_cat').trigger('change');
    });
</script>