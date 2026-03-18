<?php
ob_start();
include_once '../init.php';

$db = dbConn();

$link = "Product Management";
$breadcrumb_item = "Products";
$breadcrumb_item_active = "Edit";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('9'); // 9 is the module id for Product Management

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    extract($_GET); // to get $pid
    $sql = "SELECT * FROM items LEFT JOIN item_specs ON item_specs.ItemId=items.id WHERE items.id='$pid'";

    $result = $db->query($sql);
    $row = $result->fetch_assoc();

    $product_id = $row['id'];
    $machine_cat = $row['item_category'];
    $model_no = $row['model_no'];
    $product_name = $row['item_name'];
    $description = $row['item_description'];
    $main_image = $row['item_image'];
    $status = $row['status'];
    $capacity = $row['Capacity'];
    $channels = $row['Channels'];
    $compressor = $row['Compressor'];
    $cameras = $row['Cameras'];
    $power = $row['Power'];
    $voltage = $row['Voltage'];
    $ejectors = $row['Ejectors'];
    $weight = $row['Weight'];
    $dimensions = $row['Dimensions'];
}

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
    }
    if (empty($product_name)) {
        $message['product_name'] = "Please enter the Product name!";
    }else {
        // check if there is any other product with the same name. it cannot be allowed
        $sql = "SELECT item_name FROM items WHERE item_name='$product_name' AND id <> '$product_id'";
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
    } else { // if a image is not uploaded, previous image is assigned again
        $main_image = $prv_main_img; // prv_main_img submitted from the form as a hidden field
    }

    if (empty($message)) {
        $sql = "UPDATE items SET item_name='$product_name', model_no='$model_no', item_description='$description', "
                . "item_category='$machine_cat', item_image='$main_image', status='$status' WHERE id='$product_id'";
        $db->query($sql);

        // UPDATE specs for color sorters only
        if ($machine_cat == 1) {
            // check if existing records found
            $sql = "SELECT * FROM item_specs WHERE ItemId = '$product_id'";
            $result = $db->query($sql);
            if ($result->num_rows <= 0) { // no records
                $sql = "INSERT INTO item_specs (ItemId,Capacity,Channels,Compressor,Cameras,Power,Voltage,Ejectors,Weight,Dimensions) "
                        . "VALUES ('$product_id','$capacity','$channels','$compressor','$cameras','$power','$voltage','$ejectors','$weight','$dimensions')";
                $db->query($sql);
            } else { // update existing record
                $sql = "UPDATE item_specs SET Capacity='$capacity',Channels='$channels',Compressor='$compressor',Cameras='$cameras',"
                        . "Power='$power',Voltage='$voltage',Ejectors='$ejectors',Weight='$weight',Dimensions='$dimensions' WHERE ItemId='$product_id'";
                $db->query($sql);
            }
        }

        if (isset($_FILES['extra_images']) && !empty($_FILES['extra_images']['name'][0])) {
            $extra_images = $_FILES['extra_images'];

            // Filter out non-selected files
            $filtered_files = array_filter($extra_images['name']);
            $valid_files = [];

            foreach ($filtered_files as $key => $filename) {
                $valid_files['name'][] = $extra_images['name'][$key];
                $valid_files['type'][] = $extra_images['type'][$key];
                $valid_files['tmp_name'][] = $extra_images['tmp_name'][$key];
                $valid_files['error'][] = $extra_images['error'][$key];
                $valid_files['size'][] = $extra_images['size'][$key];
            }

            if (!empty($valid_files['name'])) {
                $location = '../../uploads/products';
                $uploadResult = uploadFiles($valid_files, $location); // Call the function to upload files
                //print_r($uploadResult);

                foreach ($uploadResult as $key => $value) {
                    // Check if the upload was successful
                    if ($value['upload']) {
                        $extra_img = $value['file'];

                        // Insert new records
                        $sql = "INSERT INTO item_images(ItemId, ImagePath) VALUES ('$product_id','$extra_img')";
                        if ($db->query($sql)) {
                            echo "Image inserted successfully.<br>";
                        } else {
                            echo "Error inserting image: " . $db->error . "<br>";
                        }
                    } else {
                        // Handle upload failure messages
                        foreach ($value as $errorType => $message) {
                            if ($errorType != 'upload') { // Avoid displaying the 'upload' key
                                echo $message . '<br>';
                            }
                        }
                    }
                }
            }
        }
    }
    //header("Location:manage.php");
}
?>

<div class="row">
    <div class="col-12">

        <a href="manage.php" class="btn btn-dark mb-2"> <i class="fas fa-eye"></i> View All</a>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit : <b><?= $product_name ?></b></h3>
            </div>
            <!--Add novalidate attribute to skip browser validations-->
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data" novalidate>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="form-group col-md-4">
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
                                <div class="form-group col-md-8">
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
                                <div class="form-group col-md-2">
                                    <label for="product_name">Capacity (kg/h)</label>
                                    <input type="text" class="form-control" id="capacity" name="capacity" placeholder="500-1000" value="<?= @$capacity ?>">
                                    <span class="error_span text-danger"><?= @$message['capacity'] ?></span>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="product_name">No. of Channels</label>
                                    <input type="text" class="form-control" id="channels" name="channels" placeholder="No. of Channels" value="<?= @$channels ?>">
                                    <span class="error_span text-danger"><?= @$message['channels'] ?></span>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="product_name">Compressor</label>
                                    <input type="text" class="form-control" id="compressor" name="compressor" placeholder="3.5 KW Piston Compressor" value="<?= @$compressor ?>">
                                    <span class="error_span text-danger"><?= @$message['compressor'] ?></span>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="product_name">No. of Cameras</label>
                                    <input type="text" class="form-control" id="cameras" name="cameras" placeholder="No. of Cameras" value="<?= @$cameras ?>">
                                    <span class="error_span text-danger"><?= @$message['cameras'] ?></span>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="product_name">Power (kW)</label>
                                    <input type="text" class="form-control" id="power" name="power" placeholder="1.5 KW" value="<?= @$power ?>">
                                    <span class="error_span text-danger"><?= @$message['power'] ?></span>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="product_name">Voltage</label>
                                    <input type="text" class="form-control" id="voltage" name="voltage" placeholder="110-220V/ 50-60Hz" value="<?= @$voltage ?>">
                                    <span class="error_span text-danger"><?= @$message['voltage'] ?></span>
                                </div>                        
                                <div class="form-group col-md-2">
                                    <label for="product_name">No. of Ejectors</label>
                                    <input type="text" class="form-control" id="ejectors" name="ejectors" placeholder="64/128/256" value="<?= @$ejectors ?>">
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
                                    <textarea class="form-control" id="description" name="description" placeholder="Enter Product Description" style="height: 400px"><?= @$description ?></textarea>
                                    <span class="error_span text-danger"><?= @$message['description'] ?></span>
                                </div>
                            </div>
                            <div class="row">                                
                                <div class="col-md-12">
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

                        <!--product images section-->
                        <div class="col-md-4">
                            <!--display existing main product image-->
                            <div class="row">                                
                                <img src="../../uploads/products/<?= $main_image ?>" width="100%"/>
                                <!-- have to send the existing product image in a hidden field-->
                                <input type="hidden" name="prv_main_img" value="<?= $main_image ?>">

                                <h5>Change Main Product Image</h5>
                                <div class="input-group">
                                    <input type="file" class="form-control mb-2" id="main_image" name="main_image">                            
                                </div>
                                <span class="error_span text-danger"><?= @$message['main_image'] ?></span>
                            </div>

                            <!--Additional product images-->
                            <div class="row">
                                <?php
                                $sql = "SELECT * From item_images WHERE ItemId = '$product_id' ORDER BY ImageId DESC LIMIT 3";
                                $result = $db->query($sql);
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        ?>
                                        <div class="col-md-4">
                                            <img src="../../uploads/products/<?= $row['ImagePath'] ?>" width="100%" />
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            <div class="mt-3">
                                <label>Change Additional Product Images</label>
                                <div class="input-group">
                                    <input type="file" class="form-control mb-2" id="extra_image1" name="extra_images[]">
                                </div>
                                <!--                                <div class="input-group">
                                                                    <input type="file" class="form-control mb-2" id="extra_image2" name="extra_images[]">
                                                                </div>
                                                                <div class="input-group">
                                                                    <input type="file" class="form-control mb-2" id="extra_image3" name="extra_images[]">
                                                                </div>-->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <input type="hidden" name='product_id' value="<?= $product_id ?>">
                    <button type="submit" class="btn btn-primary" <?= $privilege['Edit'] == '0' ? 'disabled' : '' ?>>Update Product</button>
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
