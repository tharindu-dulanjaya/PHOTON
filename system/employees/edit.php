<?php
ob_start();
include_once '../init.php';

$db = dbConn();

$link = "Employee Management";
$breadcrumb_item = "Employees";
$breadcrumb_item_active = "Update";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('1'); // 1 is the module id for Employee Management

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    extract($_GET); //to get the employee id
    $sql = "SELECT * FROM users u "
            . "INNER JOIN employees e ON e.UserId=u.UserId "
            . "LEFT JOIN technicians t ON t.EmpId=e.EmployeeId "
            . "WHERE u.UserId='$userid'";

    $result = $db->query($sql);
    $row = $result->fetch_assoc();

    $UserId = $row['UserId']; //to send in a hidden variable when submitting the form
    $first_name = $row['FirstName'];
    $last_name = $row['LastName'];
    $designation_id = $row['DesignationId'];
    $department_id = $row['DepartmentId'];
    $app_date = $row['AppointmentDate'];
    $email = $row['Email'];
    $nic = $row['Nic'];
    $mobile_no = $row['MobileNo'];
    $address_line1 = $row['AddressLine1'];
    $address_line2 = $row['AddressLine2'];
    $city = $row['City'];
    $district = $row['DistrictId'];
    $title = $row['TitleId'];
    $gender = $row['GenderId'];
    $skilled_cat = $row['SkilledCategory'];
    $skill_level = $row['SkillLevel'];
}

//check when submitting data after update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);
    $first_name = dataClean($first_name);
    $last_name = dataClean($last_name);
    $designation_id = dataClean($designation_id);
    $department_id = dataClean($department_id);
    $app_date = dataClean($app_date);
    $email = dataClean($email);
    $nic = dataClean($nic);
    $mobile_no = dataClean($mobile_no);
    $address_line1 = dataClean($address_line1);
    $address_line2 = dataClean($address_line2);
    $city = dataClean($city);

    $message = array();

    if (empty($title)) {
        $message['title'] = "Select your Title";
    }
    if (empty($first_name)) {
        $message['first_name'] = "The First Name should not be blank!";
    } else {
        if (ctype_alpha(str_replace(' ', '', $first_name)) === false) {
            $message['first_name'] = "Only letters and white spaces are allowed";
        }
    }
    if (empty($last_name)) {
        $message['last_name'] = "The Last Name should not be blank!";
    } else {
        if (ctype_alpha(str_replace(' ', '', $last_name)) === false) {
            $message['last_name'] = "Only letters and white spaces are allowed";
        }
    }
    if (empty($designation_id)) {
        $message['designation_id'] = "The Designation should not be blank!";
    } else {
        if ($designation_id == 7) { // check if the designation is "Technician"
            if (empty($skilled_cat)) {
                $message['skilled_cat'] = "Skilled category cannot be empty!";
            }
            if (empty($skill_level)) {
                $message['skill_level'] = "Please select approximate skill level!";
            }
        }
    }
    if (empty($department_id)) {
        $message['department_id'] = "The Department should not be blank!";
    }
    if (empty($app_date)) {
        $message['app_date'] = "The Appointment Date should not be blank!";
    }
    if (empty($address_line1)) {
        $message['address_line1'] = "Address Line 1 cannot be empty!";
    }
    if (empty($address_line2)) {
        $message['address_line2'] = "Address Line 2 cannot be empty!";
    }
    if (empty($city)) {
        $message['city'] = "City cannot be empty!";
    }
    if (empty($district)) {
        $message['district'] = "Select employee district";
    }
    if (!isset($gender)) {
        $message['gender'] = "Gender is required!";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message['email'] = "Invalid Email Address...!";
    } else {
        //check if the email address already exists or not                        
        $db = dbConn();
        $sql = "SELECT * FROM users WHERE Email='$email' AND UserId <> '$UserId'";
        $result = $db->query($sql);
        if ($result->num_rows > 0) {
            $message['email'] = "This Email address already exists!";
        }
    }

    // validate mobile number
    if (!empty($mobile_no)) {
        $mobile_no = trim($mobile_no); // remove leading and trailing whitespace
        // ctype_digit() validates, variable contains only digits.
        // Check if the number starts with +94 followed by 9 digits (total 12)
        // 0,3 means the first 3 characters starting from the begining
        if (strlen($mobile_no) === 12 && substr($mobile_no, 0, 3) === '+94' && ctype_digit(substr($mobile_no, 3))) {
            // mobile number is okay
        } else {
            $message['mobile_no'] = "Invalid mobile number.!";
        }
    } else {
        $message['mobile_no'] = "Please enter Mobile number!";
    }

    // validate NIC number
    if (!empty($nic)) {
        // check for old NIC format
        if (strlen($nic) == 10) {
            $firstPart = substr($nic, 0, 9);
            // use strtoupper() function to convert v to V and x to X
            // -1 means the final character starting from the end
            $lastChar = strtoupper(substr($nic, -1));

            // ctype_digit() is used to check if all characters are numeric
            // check if the first part is numeric and the last character is 'V' or 'X'
            if (ctype_digit($firstPart) && ($lastChar == 'V' || $lastChar == 'X')) {
                // Valid old NIC format
            } else {
                $message['nic'] = "Invalid NIC";
            }
        } elseif ($nic == 12) { // check for new NIC format
            // check if all characters are numeric
            if (ctype_digit($nic)) {
                // Valid new NIC format
            } else {
                $message['nic'] = "Invalid NIC format";
            }
        } else {
            $message['nic'] = "Invalid NIC!";
        }
    } else {
        $message['nic'] = "Please enter NIC number!";
    }

    // check if the nic already exists in other user
    if (!empty($nic)) {
        $sql = "SELECT * FROM users WHERE Nic='$nic' AND UserId <> '$UserId'";
        $result = $db->query($sql);
        if ($result->num_rows > 0) {
            $message['nic'] = "This NIC already exists!";
        }
    }

    if (empty($message)) {
        $sql = "UPDATE users SET TitleId='$title',FirstName='$first_name',LastName='$last_name',Email='$email',Nic='$nic',AddressLine1='$address_line1',AddressLine2='$address_line2',City='$city',MobileNo='$mobile_no',GenderId='$gender',DistrictId='$district' WHERE UserId='$UserId'";
        $db->query($sql);

        $sql = "UPDATE employees SET AppointmentDate='$app_date',DesignationId='$designation_id',DepartmentId='$department_id' WHERE UserId='$UserId'";
        $db->query($sql);

        // if Technician, skill category and level should be stored
        if ($designation_id == 7) {
            // have to find the employee id to update technicians table
            $sql = "SELECT EmployeeId FROM employees WHERE UserId='$UserId'";
            $result = $db->query($sql);
            $row = $result->fetch_assoc();
            $EmpId = $row['EmployeeId'];

            $sql = "UPDATE technicians SET SkilledCategory='$skilled_cat', SkillLevel='$skill_level' WHERE EmpId='$EmpId'";
            $db->query($sql);
        }

        header("Location:manage.php");
    }
}
?>
<div class="row">
    <div class="col-12">

        <a href="manage.php" class="btn btn-dark mb-2"><i class="fas fa-users"></i> View All</a>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Update Employee</h3>
            </div>              
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-2 ">
                            <?php
                            $sql = "SELECT * FROM titles";
                            $result = $db->query($sql);
                            ?>
                            <label for="title">Mr/Mrs/..</label>
                            <select name="title" id="title" class="form-control">
                                <option value="">Select your Title</option>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <!--use ternary operator to insert the selected attribute-->
                                    <option value="<?= $row['Id'] ?>" <?= $title == $row['Id'] ? 'selected' : '' ?>> <?= $row['Title'] ?> </option>
                                    <?php
                                }
                                ?>
                            </select>
                            <span class="error_span text-danger mt-4"><?= @$message['title'] ?></span><br>
                        </div>
                        <div class="form-group col-md-5">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter First Name" value="<?= $first_name ?>">
                            <span class="error_span text-danger"><?= @$message['first_name'] ?></span>
                        </div>
                        <div class="form-group col-md-5">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter Last Name" value="<?= $last_name ?>">
                            <span class="error_span text-danger"><?= @$message['last_name'] ?></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-5">
                            <label for="designation_id">Designation</label>
                            <?php
                            if($designation_id=='1'){ //means admin
                                $sql = "SELECT * FROM designations WHERE Id='1'"; // admin designation cannot be changed
                            }else{ // not the admin
                                $sql = "SELECT * FROM designations WHERE Id != '1'"; // cannot assign as admin
                            }
                            $result = $db->query($sql);
                            ?>
                            <select class="form-control" id="designation_id" name="designation_id">
                                <option value="">--</option>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <option value="<?= $row['Id'] ?>" <?= $designation_id == $row['Id'] ? 'selected' : '' ?>><?= $row['Designation'] ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                            <span class="error_span text-danger"><?= @$message['designation_id'] ?></span>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="department_id">Department</label>
                            <?php
                            if($designation_id=='1'){ //means admin
                                $sql = "SELECT * FROM departments WHERE Id='1'"; // department can be only Administration
                            }else{
                                $sql = "SELECT * FROM departments";
                            }
                            
                            $result = $db->query($sql);
                            ?>
                            <select class="form-control" id="department_id" name="department_id">
                                <option value="">--</option>
                                <?php while ($row = $result->fetch_assoc()) { ?>
                                    <option value="<?= $row['Id'] ?>" <?= $department_id == $row['Id'] ? 'selected' : '' ?>><?= $row['Department'] ?></option>
                                <?php } ?>
                            </select>
                            <span class="error_span text-danger"><?= @$message['department_id'] ?></span>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="app_date">Appointment Date</label>
                            <input type="date" class="form-control" max="<?= date('Y-m-d'); ?>" id="app_date" name="app_date" value="<?= $app_date ?>">
                            <span class="error_span text-danger"><?= @$message['app_date'] ?></span>
                        </div>
                    </div>
                    <div id="technicianSpecs" class="row mt-3" style="display: none;">
                        <div class="form-group col-md-5">
                            <label for="skilled_cat">Skilled Product Category</label>
                            <?php
                            $sql = "SELECT * FROM item_category WHERE status = 1";
                            $result = $db->query($sql);
                            ?>
                            <select class="form-control" id="skilled_cat" name="skilled_cat">
                                <option value="">--</option>
                                <?php while ($row = $result->fetch_assoc()) { ?>
                                    <option value="<?= $row['id'] ?>" <?= @$skilled_cat == $row['id'] ? 'selected' : '' ?>><?= $row['category_name'] ?></option>
                                <?php } ?>
                            </select>
                            <span class="error_span text-danger"><?= @$message['skilled_cat'] ?></span>
                        </div>
                        <div class="form-group col-md-5">
                            <label for="skill_level">Skill Level (Approximate)</label>
                            <?php
                            $sql = "SELECT * FROM technician_skill_levels";
                            $result = $db->query($sql);
                            ?>
                            <select class="form-control" id="skill_level" name="skill_level">
                                <option value="">Select the Technician's Skill level</option>
                                <?php while ($row = $result->fetch_assoc()) { ?>
                                    <option value="<?= $row['SkillLevelValue'] ?>" <?= @$skill_level == $row['SkillLevelValue'] ? 'selected' : '' ?>><?= $row['SkillLevelValue'] ?> %</option>
                                <?php } ?>                               
                            </select>
                            <span class="error_span text-danger"><?= @$message['skill_level'] ?></span>
                        </div>
                        <div class="form-group col-md-2">

                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="form-group col-md-4">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" name="email" id="email" placeholder="Email" value="<?= $email ?>" required>
                            <span class="error_span text-danger"><?= @$message['email'] ?></span><br>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="mobile_no">Mobile Number (+94)</label>
                            <input type="text" class="form-control" name="mobile_no" id="mobile_no" placeholder="Mobile Number" value="<?= $mobile_no ?>" required>
                            <span class="error_span text-danger"><?= @$message['mobile_no'] ?></span><br>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="nic">NIC Number</label>
                            <input type="text" class="form-control" name="nic" id="nic" placeholder="National ID" value="<?= $nic ?>" required>
                            <span class="error_span text-danger"><?= @$message['nic'] ?></span><br>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="address_line1">Address Line 1</label>
                            <input type="text" class="form-control" name="address_line1" id="address_line1" placeholder="Address Line 1" value="<?= $address_line1 ?>" required>
                            <span class="error_span text-danger"><?= @$message['address_line1'] ?></span><br>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="address_line2">Address Line 2</label>
                            <input type="text" class="form-control" name="address_line2" id="address_line2" placeholder="Address Line 2" value="<?= $address_line2 ?>" required>
                            <span class="error_span text-danger"><?= @$message['address_line2'] ?></span><br>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="city">City</label>
                            <input type="text" class="form-control" name="city" id="city" placeholder="City" value="<?= $city ?>" required>
                            <span class="error_span text-danger"><?= @$message['city'] ?></span><br>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <?php
                            $sql = "SELECT * FROM districts";
                            $result = $db->query($sql);
                            ?>
                            <label>District</label>
                            <select name="district" id="district" class="form-control">
                                <option value="">Select Your District</option>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <option value="<?= $row['Id'] ?>" <?= $district == $row['Id'] ? 'selected' : '' ?>> <?= $row['Name'] ?> </option>
                                    <?php
                                }
                                ?>
                            </select>
                            <span class="error_span text-danger mt-4"><?= @$message['district'] ?></span><br>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Select Gender</label>
                            <?php
                            $sql = "SELECT * FROM genders";
                            $result = $db->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="<?= $row['Gender'] ?>" value="<?= $row['Id'] ?>" <?= @$gender == $row['Id'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="<?= $row['Gender'] ?>"> <?= $row['Gender'] ?> </label>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="error_span text-danger mt-4"><?= @$message['gender'] ?></div><br>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <!--$UserId must be passed when submitting the form by POST method to use the variable in the sql queries-->
                    <input type="hidden" name="UserId" value="<?= $UserId ?>">
                    <button type="submit" class="btn btn-primary" <?= $privilege['Edit'] == '0' ? 'disabled' : '' ?>>Update Employee</button>
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
        // when the designation changes, check if it is 'Technician'
        $('#designation_id').change(function () {
            const selectedValue = $(this).val();
            if (selectedValue === '7') { //  Technician is selected
                $('#technicianSpecs').show(); // show the specs div
            } else {
                $('#technicianSpecs').hide();
            }
        });

        // trigger change event on page load to set the initial state
        $('#designation_id').trigger('change');
    });
</script>