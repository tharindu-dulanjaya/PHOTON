<?php
ob_start();
include_once '../init.php';
include '../../mail.php';
$db = dbConn();

$link = "Add New Technician";
$breadcrumb_item = "Technician";
$breadcrumb_item_active = "New";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('11'); // Technician Management

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);
    $first_name = dataClean($first_name);
    $last_name = dataClean($last_name);
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
        $message['first_name'] = "The First Name should not be blank...!";
    } else {
        if (ctype_alpha(str_replace(' ', '', $first_name)) === false) {
            $message['first_name'] = "Only letters and white spaces are allowed";
        }
    }
    if (empty($last_name)) {
        $message['last_name'] = "The Last Name should not be empty!";
    } else {
        if (ctype_alpha(str_replace(' ', '', $last_name)) === false) {
            $message['last_name'] = "Only letters and white spaces are allowed";
        }
    }
    if (empty($skilled_cat)) {
        $message['skilled_cat'] = "Skilled category cannot be empty!";
    }
    if (empty($skill_level)) {
        $message['skill_level'] = "Please select approximate skill level!";
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
        $message['email'] = "Invalid Email Address!";
    } else {
        //check if the email address already exists or not                        
        $db = dbConn();
        $sql = "SELECT * FROM users WHERE Email='$email'";
        $result = $db->query($sql);
        if ($result->num_rows > 0) {
            $message['email'] = "Email address already exists!";
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
        $sql = "SELECT * FROM users WHERE Nic='$nic'";
        $result = $db->query($sql);
        if ($result->num_rows > 0) {
            $message['nic'] = "This NIC already exists!";
        }
    }

    if (empty($message)) {
        // create a token to verify the user
        // generates a random binary number & converts to hexa decimal
        $token = bin2hex(random_bytes(16));

        $sql = "INSERT INTO users(TitleId,FirstName,LastName,Email,Nic,AddressLine1,AddressLine2,City,MobileNo,GenderId,DistrictId,UserType,Token) "
                . "VALUES ('$title','$first_name','$last_name','$email','$nic','$address_line1','$address_line2','$city','$mobile_no','$gender','$district','employee','$token')";
        $db->query($sql);
        $UserId = $db->insert_id;

        $sql = "INSERT INTO employees(AppointmentDate,DesignationId,DepartmentId,UserId) "
                . "VALUES ('$app_date','7','5','$UserId')";
        $db->query($sql);
        $EmpId = $db->insert_id;

        $sql = "INSERT INTO technicians(EmpId, SkilledCategory, SkillLevel) VALUES ('$EmpId','$skilled_cat','$skill_level')";
        $db->query($sql);

        $msg = "<h3>Registration Successful</h3>";
        $msg .= "<h4>Congratulations!!</h4>";
        $msg .= "<p>Your employee account has been successfully created. Please use the below link to update your username and password.</p>";
        $msg .= "<a href='http://localhost/photon/system/employees/complete_registration.php?token=$token'>Update Now</a>";
        sendEmail($email, $first_name, "Employee Registration", $msg);

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: '',
                html: '<h4>Verification email sent successfully!</h4>',
                showCloseButton: false,
                showConfirmButton: false,
                timer: 3500
            }).then(function() {
                window.location.href = 'manage.php';
            });
          </script>";
    }
}
?>
<div class="row">
    <div class="col-12">

        <a href="manage.php" class="btn btn-dark mb-2"><i class="fas fa-users"></i> View All</a>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Add New Technician</h3>
            </div>
            <!--Add novalidate attribute to skip browser validations-->
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-2 ">
                            <?php
                            $db = dbConn();
                            $sql = "SELECT * FROM titles";
                            $result = $db->query($sql);
                            ?>
                            <label for="title">Mr/Mrs/..</label>
                            <select name="title" id="title" class="form-control">
                                <option value="">Select Title</option>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <option value="<?= $row['Id'] ?>" <?= @$title == $row['Id'] ? 'selected' : '' ?>> <?= $row['Title'] ?> </option>
                                    <?php
                                }
                                ?>
                            </select>
                            <span class="error_span text-danger mt-4"><?= @$message['title'] ?></span><br>
                        </div>
                        <div class="form-group col-md-5">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter First Name" value="<?= @$first_name ?>">
                            <span class="error_span text-danger"><?= @$message['first_name'] ?></span>
                        </div>
                        <div class="form-group col-md-5">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter Last Name" value="<?= @$last_name ?>">
                            <span class="error_span text-danger"><?= @$message['last_name'] ?></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="app_date">Appointment Date</label>
                            <input type="date" class="form-control" max="<?= date("Y-m-d"); ?>" id="app_date" name="app_date" value="<?= @$app_date ?>">
                            <span class="error_span text-danger"><?= @$message['app_date'] ?></span>
                        </div>
                        <div class="form-group col-md-4">
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
                        <div class="form-group col-md-4">
                            <label for="skill_level">Skill Level (Approximate)</label>
                            <?php
                            $sql = "SELECT * FROM technician_skill_levels";
                            $result = $db->query($sql);
                            ?>
                            <select class="form-control" id="skill_level" name="skill_level">
                                <option value="">Select the Technician's Skill level</option>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <option value="<?= $row['SkillLevelValue'] ?>" <?= @$skill_level == $row['SkillLevelValue'] ? 'selected' : '' ?>><?= $row['SkillLevelValue'] ?> %</option>
                                    <?php
                                }
                                ?>                              
                            </select>
                            <span class="error_span text-danger"><?= @$message['skill_level'] ?></span>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="form-group col-md-4">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" name="email" id="email" placeholder="Email" value="<?= @$email ?>" required>
                            <span class="error_span text-danger"><?= @$message['email'] ?></span><br>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="mobile_no">Mobile Number (+94)</label>
                            <input type="text" class="form-control border border-1 border-dark-subtle" name="mobile_no" id="mobile_no" placeholder="+94712219621" value="<?= @$mobile_no ?>" required>
                            <span class="error_span text-danger"><?= @$message['mobile_no'] ?></span><br>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="nic">NIC Number</label>
                            <input type="text" class="form-control" name="nic" id="nic" placeholder="National ID" value="<?= @$nic ?>" required>
                            <span class="error_span text-danger"><?= @$message['nic'] ?></span><br>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="address_line1">Address Line 1</label>
                            <input type="text" class="form-control" name="address_line1" id="address_line1" placeholder="Address Line 1" value="<?= @$address_line1 ?>" required>
                            <span class="error_span text-danger"><?= @$message['address_line1'] ?></span><br>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="address_line2">Address Line 2</label>
                            <input type="text" class="form-control" name="address_line2" id="address_line2" placeholder="Address Line 2" value="<?= @$address_line2 ?>" required>
                            <span class="error_span text-danger"><?= @$message['address_line2'] ?></span><br>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="city">City</label>
                            <input type="text" class="form-control" name="city" id="city" placeholder="City" value="<?= @$city ?>" required>
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
                                <option value="">Select Employee District</option>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <option value="<?= $row['Id'] ?>" <?= @$district == $row['Id'] ? 'selected' : '' ?>> <?= $row['Name'] ?> </option>
                                    <?php
                                }
                                ?>
                            </select>
                            <span class="error_span text-danger mt-4"><?= @$message['district'] ?></span><br>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Employee Gender</label>
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
                    <button type="submit" class="btn btn-primary" <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>>Add Technician</button>
                </div>
            </form>

        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../layouts.php';
?>