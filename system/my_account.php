<?php
ob_start();
include_once 'init.php';

//If a session is not already created, start a session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// if a user is not logged in, USERID session is not created. if so, nothing is allowed to access
if (!isset($_SESSION['USERID'])) {
    header("Location:http://localhost/photon/system/login.php");
    return;
}

$db = dbConn();

$link = "My Account";
$breadcrumb_item = "Dashboard";
$breadcrumb_item_active = "Profile";

$userid = $_SESSION['USERID'];
$sql = "SELECT * FROM users u WHERE u.UserId='$userid'";
$result = $db->query($sql);
$row = $result->fetch_assoc();

$UserId = $row['UserId'];
$first_name = $row['FirstName'];
$last_name = $row['LastName'];
$profile_image = $row['UserImage'];
$email = $row['Email'];
$nic = $row['Nic'];
$mobile_no = $row['MobileNo'];
$address_line1 = $row['AddressLine1'];
$address_line2 = $row['AddressLine2'];
$city = $row['City'];
$district = $row['DistrictId'];
$title = $row['TitleId'];
$gender = $row['GenderId'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['operate'] == 'change_details') {
    extract($_POST);

    $first_name = dataClean($first_name);
    $last_name = dataClean($last_name);
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
        $message['last_name'] = "The Last Name should not be blank...!";
    } else {
        if (ctype_alpha(str_replace(' ', '', $last_name)) === false) {
            $message['last_name'] = "Only letters and white spaces are allowed";
        }
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
        $message['email'] = "Invalid Email Address...!";
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
        } elseif (strlen($nic) == 12) { // check for new NIC format
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

    //profile image upload
    if (!empty($_FILES['profile_img']['name']) && empty($message)) {
        $file = $_FILES['profile_img'];
        $location = "../uploads/profiles";
        $uploadResult = uploadFile($file, $location);
        if ($uploadResult['upload']) {
            $userImage = $uploadResult['file'];
        } else {
            $error = $uploadResult['error_file'];
            $message['profile_img'] = "<br>Image upload failed : $error";
        }
    } else {// if a image is not uploaded, previous image is assigned again
        $userImage = $prv_profile_img;
    }

    if (empty($message)) {
        $sql = "UPDATE users SET TitleId='$title',FirstName='$first_name',LastName='$last_name',UserImage='$userImage',Email='$email',Nic='$nic',AddressLine1='$address_line1',AddressLine2='$address_line2',City='$city',MobileNo='$mobile_no',GenderId='$gender',DistrictId='$district' WHERE UserId='$UserId'";
        $result = $db->query($sql);
        // if the query executed successfully it will return true
        if ($result) {
            echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Update Successful!',
                        showCloseButton: false,
                        showConfirmButton: false,
                        timer: 2000
                        }).then(function() {
                            window.location.href = 'http://localhost/photon/system/my_account.php';
                        });
                    </script>";
        } else {
            echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        showCloseButton: false,
                        showConfirmButton: false,
                        timer: 2000
                        });
                    </script>";
        }
    } else {
        // $message is not empty. there are errors
        echo "<script>
                    Swal.fire({
                        icon: 'warning',
                        title: 'You have some errors!',
                        showCloseButton: false,
                        showConfirmButton: false,
                        timer: 2000
                        });
                    </script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['operate'] == 'change_password') {
    extract($_POST);

    $message = array();

    if (empty($old_password)) {
        $message['old_password'] = "Please enter your existing password";
    }
    if (empty($new_password)) {
        $message['new_password'] = "Enter your new password!";
    } else {
        // password strength
        $uppercase = preg_match('@[A-Z]@', $new_password);
        $lowercase = preg_match('@[a-z]@', $new_password);
        $number = preg_match('@[0-9]@', $new_password);
        $specialChars = preg_match('@[^\w]@', $new_password);

        if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($new_password) < 8) {
            $message['new_password'] = 'New Password should be at least 8 characters long, should include at least one uppercase letter, one lowercase letter, one number, and one special character!';
        } else {
            if (empty($confirm_password)) {
                $message['confirm_password'] = "Please confirm your password!";
            } else {
                if ($new_password != $confirm_password) {
                    $message['confirm_password'] = "Passwords do not match!";
                }
            }
        }
    }

    if (empty($message)) {
        $sql = "SELECT Password FROM users WHERE UserId='$UserId'";
        $result = $db->query($sql);

        if ($result->num_rows == 1) {  // if true, there is an actual user
            $row = $result->fetch_assoc(); //no while loop is used bcz only one row
            //inbuilt function to verify pwd
            if (password_verify($old_password, $row['Password'])) {
                // entered existing password is correct
                // encrypt new password using bcrypt hashing algorithem
                $pwd = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET Password='$pwd' WHERE UserId='$UserId'";
                $result = $db->query($sql);
                // if the query executed successfully it will return true
                if ($result) {
                    echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Password changed!',
                        showCloseButton: false,
                        showConfirmButton: false,
                        timer: 2000
                        });
                    </script>";
                }
            } else {
                $message['old_password'] = "Old password is incorrect!!";
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Old password incorrect!',
                        showCloseButton: false,
                        showConfirmButton: false,
                        timer: 2000
                        });
                    </script>";
            }
        }
    } else {
        // $message is not empty. there are errors
        echo "<script>
                    Swal.fire({
                        icon: 'warning',
                        title: 'You have some errors!',
                        showCloseButton: false,
                        showConfirmButton: false,
                        timer: 2000
                        });
                    </script>";
    }
}
?>

<!--form to update profile pic and personal details-->
<form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data" novalidate>
    <div class="row">
        <div class="col-3">
            <div class="card card-primary sticky-top">
                <div class="card-body">
                    <!-- if user has no image, default image will be shown-->
                    <img src="../uploads/profiles/<?= $profile_image == null ? 'userprofile.png' : $profile_image ?>" width="100%" class="mb-3"/>
                    <!-- have to send the existing profile image in a hidden field-->
                    <input type="hidden" name="prv_profile_img" value="<?= $profile_image ?>">
                    <h5>Change Profile Photo</h5>
                    <div class="input-group mb-3">
                        <input type="file" class="form-control mb-2" id="profile_img" name="profile_img">
                        <span class="error_span text-danger"><?= @$message['profile_img'] ?></span><br>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-9">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Personal Details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-2 ">
                            <?php
                            $sql = "SELECT * FROM titles";
                            $result = $db->query($sql);
                            ?>
                            <label for="title">Mr/Mrs/..</label>
                            <select name="title" id="title" class="form-control">
                                <option value="">Select Title</option>
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
                        <div class="form-group col-md-4">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" name="email" id="email" placeholder="Email" value="<?= $email ?>" required>
                            <span class="error_span text-danger"><?= @$message['email'] ?></span><br>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="mobile_no">Mobile Number (+94)</label>
                            <!--<div class="input-group">-->
                                <!--<span class="input-group-text">+ 94 </span>-->
                            <input type="text" class="form-control" name="mobile_no" id="mobile_no" placeholder="712219621" value="<?= $mobile_no ?>" required>
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
                    <input type="hidden" name="operate" value="change_details">
                    <button type="submit" class="btn btn-primary">Update Details</button>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="row">
    <div class="col-3">
    </div>
    <div class="col-9">
        <div class="card card-dark">
            <div class="card-header">
                <h3 class="card-title">Change Password</h3>
            </div> 
            <!--form to update the password-->
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="Password">Existing Password</label>
                            <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Your Existing Password" value="<?= @$old_password ?>" required>
                            <span class="error_span text-danger"><?= @$message['old_password'] ?></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="Password">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Your New Password" value="<?= @$new_password ?>" required>
                            <span class="error_span text-danger"><?= @$message['new_password'] ?></span>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="confirm_password">Confirm Your New Password</label>
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm your new Password" required>
                            <span class="error_span text-danger"><?= @$message['confirm_password'] ?></span><br>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <!--$UserId must be passed when submitting the form by POST method to use the variable in the sql queries-->
                    <input type="hidden" name="UserId" value="<?= $UserId ?>">
                    <input type="hidden" name="operate" value="change_password">
                    <button type="submit" class="btn btn-dark">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php
$content = ob_get_clean();
include 'layouts.php';
?>