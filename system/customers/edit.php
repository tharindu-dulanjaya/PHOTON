<?php
ob_start();
include_once '../init.php';

$db = dbConn();

$link = "Customer Management";
$breadcrumb_item = "Customers";
$breadcrumb_item_active = "Update";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('2'); 

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    extract($_GET); //to get the employee id
    $sql = "SELECT * FROM users u INNER JOIN customers c ON c.UserId=u.UserId WHERE u.UserId='$userid'";

    $result = $db->query($sql);
    $row = $result->fetch_assoc();

    $UserId = $row['UserId'];
    $first_name = $row['FirstName'];
    $last_name = $row['LastName'];
    $email = $row['Email'];
    $nic = $row['Nic'];
    $mobile_no = $row['MobileNo'];
    $telno = $row['TelNo'];
    $mill_name = $row['MillName'];
    $address_line1 = $row['AddressLine1'];
    $address_line2 = $row['AddressLine2'];
    $city = $row['City'];
    $district = $row['DistrictId'];
    $title = $row['TitleId'];
    $gender = $row['GenderId'];
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);
    $first_name = dataClean($first_name);
    $last_name = dataClean($last_name);
    $email = dataClean($email);
    $mill_name = dataClean($mill_name);
    $address_line1 = dataClean($address_line1);
    $address_line2 = dataClean($address_line2);
    $city = dataClean($city);
    $telno = dataClean($telno);
    $mobile_no = dataClean($mobile_no);

    $message = array();
    // Check validations
    if (empty($title)) {
        $message['title'] = "Select your Title";
    }
    if (empty($first_name)) {
        $message['first_name'] = "The first name should not be blank!";
    } else {
        if (ctype_alpha(str_replace(' ', '', $first_name)) === false) {
            $message['first_name'] = "Only letters and white spaces are allowed";
        }
    }
    if (empty($last_name)) {
        $message['last_name'] = "The last name should not be blank!";
    } else {
        if (ctype_alpha(str_replace(' ', '', $last_name)) === false) {
            $message['last_name'] = "Only letters and white spaces are allowed";
        }
    }
    if (empty($mill_name)) {
        $message['mill_name'] = "Please enter your Mill or Company name";
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
        $message['district'] = "Please select your District";
    }
    if (!isset($gender)) {
        $message['gender'] = "Gender is required!";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message['email'] = "Invalid Email Address!";
    } else {
        //check if the email address already exists or not
        $sql = "SELECT * FROM users WHERE Email='$email' AND UserId <> '$UserId'";
        $result = $db->query($sql);

        if ($result->num_rows > 0) {
            $message['email'] = "This Email address already exists...!";
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
    
    // validate telephone number
    if (!empty($telno)) {
        $mobile_no = trim($telno); // remove leading and trailing whitespace
        // ctype_digit() validates, variable contains only digits.
        // Check if the number starts with +94 followed by 9 digits (total 12)
        // 0,3 means the first 3 characters starting from the begining
        if (strlen($telno) === 12 && substr($telno, 0, 3) === '+94' && ctype_digit(substr($telno, 3))) {
            // telephone number is okay
        } else {
            $message['telno'] = "Invalid telephone number!";
        }
    } else {
        $message['telno'] = "Please enter telephone number!";
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

        $sql = "UPDATE customers SET MillName='$mill_name',TelNo='$telno' WHERE UserId='$UserId'";
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
                <h3 class="card-title">Edit Customer</h3>
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
                                <option value="">Title</option>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <!-- $title is created only after the submit button is clicked. therefore have to put @ before the variable-->
                                    <option value="<?= $row['Id'] ?>" <?= $title == $row['Id'] ? 'selected' : '' ?>> <?= $row['Title'] ?> </option>
                                    <?php
                                }
                                ?>
                            </select>
                            <span class="error_span text-danger mt-4"><?= @$message['title'] ?></span><br>
                        </div>
                        <div class="form-group col-md-5 ">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name" class="form-control" id="first_name" placeholder="First Name" value="<?= $first_name ?>" required>
                            <span class="error_span text-danger"><?= @$message['first_name'] ?></span>
                        </div>
                        <div class="form-group col-md-5 ">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" value="<?= $last_name ?>" required>
                            <span class="error_span text-danger"><?= @$message['last_name'] ?></span><br>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" name="email" id="email" placeholder="Email" value="<?= $email ?>" required>
                            <span class="error_span text-danger"><?= @$message['email'] ?></span><br>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="nic">NIC Number</label>
                            <input type="text" class="form-control" name="nic" id="nic" placeholder="National ID" value="<?= $nic ?>" required>
                            <span class="error_span text-danger"><?= @$message['nic'] ?></span><br>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="mill_name">Mill / Company Name</label>
                            <input type="text" class="form-control border border-1 border-dark-subtle" name="mill_name" id="mill_name" placeholder="Mill or Company Name" value="<?= $mill_name ?>" required>
                            <span class="error_span text-danger"><?= @$message['mill_name'] ?></span><br>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="address_line1">Address Line 1</label>
                            <input type="text" class="form-control border border-1 border-dark-subtle" name="address_line1" id="address_line1" placeholder="Address Line 1" value="<?= $address_line1 ?>" required>
                            <span class="error_span text-danger"><?= @$message['address_line1'] ?></span><br>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="address_line2">Address Line 2</label>
                            <input type="text" class="form-control border border-1 border-dark-subtle" name="address_line2" id="address_line2" placeholder="Address Line 2" value="<?= $address_line2 ?>" required>
                            <span class="error_span text-danger"><?= @$message['address_line2'] ?></span><br>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="city">City</label>
                            <input type="text" class="form-control border border-1 border-dark-subtle" name="city" id="city" placeholder="City" value="<?= $city ?>" required>
                            <span class="error_span text-danger"><?= @$message['city'] ?></span><br>
                        </div>
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
                    </div>
                    <div class="row">
                        <div class="form-group col-md-2">
                            <label>Select Gender</label>
                            <?php
                            $sql = "SELECT * FROM genders";
                            $result = $db->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="<?= $row['Gender'] ?>" value="<?= $row['Id'] ?>" <?= $gender == $row['Id'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="<?= $row['Gender'] ?>"> <?= $row['Gender'] ?> </label>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="error_span text-danger mt-4"><?= @$message['gender'] ?></div><br>
                        </div>
                        <div class="form-group col-md-5 ">
                            <label for="telno">Telephone - Mill (+94)</label>
                            <input type="text" class="form-control border border-1 border-dark-subtle" name="telno" id="telno" placeholder="Phone Number (Mill or Company)" value="<?= $telno ?>" required>
                            <span class="error_span text-danger"><?= @$message['telno'] ?></span><br>
                        </div>
                        <div class="form-group col-md-5">
                            <label for="mobile_no">Mobile Number (+94)</label>
                            <input type="text" class="form-control border border-1 border-dark-subtle" name="mobile_no" id="mobile_no" placeholder="Mobile Number" value="<?= $mobile_no ?>" required>
                            <span class="error_span text-danger"><?= @$message['mobile_no'] ?></span><br>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <!--$UserId must be passed when submitting the form by POST method to use the variable in the sql queries-->
                    <input type="hidden" name="UserId" value="<?= $UserId ?>">
                    <button type="submit" class="btn btn-primary" <?= $privilege['Edit'] == '0' ? 'disabled' : '' ?>>Update Customer</button>
                </div>
            </form>            
        </div>
    </div>
</div>


<?php
$content = ob_get_clean();
include '../layouts.php';
?>