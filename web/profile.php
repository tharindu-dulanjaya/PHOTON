<?php
ob_start();
include 'dashboard_header.php'; // header has the init.php 
$db = dbConn();

$userid = $_SESSION['USERID'];

$sql = "SELECT * FROM users INNER JOIN customers ON customers.UserId = users.UserId WHERE users.UserId='$userid'";
$result = $db->query($sql);
$row = $result->fetch_assoc();

$UserId = $row['UserId']; //to send in post method as hidden field
$customerid = $row['CustomerId'];
$title = $row['TitleId'];
$first_name = $row['FirstName'];
$last_name = $row['LastName'];
$email = $row['Email'];
$nic = $row['Nic'];
$mill_name = $row['MillName'];
$address_line1 = $row['AddressLine1'];
$address_line2 = $row['AddressLine2'];
$city = $row['City'];
$district = $row['DistrictId'];
$telno = $row['TelNo'];
$mobile_no = $row['MobileNo'];
$gender = $row['GenderId'];
$profile_image = $row['UserImage']; // to set the user image when loading

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['operate'] == 'change_details') {
    extract($_POST);
    $first_name = dataClean($first_name);
    $last_name = dataClean($last_name);
    $email = dataClean($email);
    $nic = dataClean($nic);
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
        $message['first_name'] = "The first name should not be blank...!";
    } else {
        //Advanced validation
        //str_replace() -> replace characters
        //ctype_alpha() -> returns true if every character in the string is an alphabet character (a-z, A-Z), and false otherwise.
        if (ctype_alpha(str_replace(' ', '', $first_name)) === false) {
            $message['first_name'] = "Only letters and white spaces are allowed";
        }
    }
    if (empty($last_name)) {
        $message['last_name'] = "The last name should not be blank...!";
    } else {
        if (ctype_alpha(str_replace(' ', '', $last_name)) === false) {
            $message['last_name'] = "Only letters and white spaces are allowed";
        }
    }
    if (empty($mill_name)) {
        $message['mill_name'] = "Please enter the Company or Mill name";
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
            $message['email'] = "This Email address already exists!";
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
        $sql = "UPDATE users SET UserImage='$userImage', TitleId='$title', FirstName='$first_name', LastName='$last_name', Email='$email', Nic='$nic', AddressLine1='$address_line1', AddressLine2='$address_line2', City='$city', MobileNo='$mobile_no', GenderId='$gender', DistrictId='$district' WHERE UserId='$UserId'";
        $db->query($sql);

        $sql = "UPDATE customers SET MillName='$mill_name', TelNo='$telno' WHERE UserId='$UserId'";
        $result = $db->query($sql);
        // if the query executed successfully it will return true
        if ($result) {
            echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Details updated!',
                        showCloseButton: false,
                        showConfirmButton: false,
                        timer: 2000
                        }).then(function() {
                            window.location.href = 'http://localhost/photon/web/profile.php';
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

<main id="main">
    <!--Breadcrumb section-->
    <section class="breadcrumbs">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Profile Details</h2>
                <ol>
                    <li><b>Customer</b></li>
                    <li><a href="<?= WEB_URL ?>dashboard.php" style="color: #fff;">Dashboard</a></li>
                    <li>My Account</li>
                </ol>
            </div>
        </div>
    </section>

    <section id="contact" class="contact">
        <div class="container" data-aos="fade-up">

            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" role="form" class="php-email-form" enctype="multipart/form-data" novalidate>
                <div class="row">
                    <div class="col-md-3" data-aos="fade-right" data-aos-delay="200">
                        <!-- if user has no image, default image will be shown-->
                        <img src="../uploads/profiles/<?= $profile_image == null ? 'userprofile.png' : $profile_image ?>" width="100%" class="php-email-form mb-3"/>
                        <!-- have to send the existing profile image in a hidden field-->
                        <input type="hidden" value="<?= $profile_image ?>" name="prv_profile_img">
                        <h5>Change Profile Photo</h5>
                        <div class="input-group mb-3">
                            <input type="file" class="form-control mb-2" id="profile_img" name="profile_img">
                            <span class="error_span text-danger"><?= @$message['profile_img'] ?></span><br>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-floating">
                                    <?php
                                    $sql = "SELECT * FROM titles";
                                    $result = $db->query($sql);
                                    ?>

                                    <select name="title" id="title" class="form-select border border-1 border-dark-subtle">
                                        <option value="">Title</option>
                                        <?php
                                        while ($row = $result->fetch_assoc()) {
                                            ?>
                                            <!-- $title is created only after the submit button is clicked. therefore have to put @ before the variable-->
                                            <option value="<?= $row['Id'] ?>" <?= @$title == $row['Id'] ? 'selected' : '' ?>> <?= $row['Title'] ?> </option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <label for="title">Mr/Mrs/..</label>
                                    <span class="error_span text-danger mt-4"><?= @$message['title'] ?></span><br>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-floating">                                    
                                    <input type="text" name="first_name" class="form-control border border-1 border-dark-subtle" id="first_name" placeholder="First Name" value="<?= @$first_name ?>" required>
                                    <label for="first_name">First Name</label>
                                    <span class="error_span text-danger"><?= @$message['first_name'] ?></span>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-floating">                                    
                                    <input type="text" class="form-control border border-1 border-dark-subtle" name="last_name" id="last_name" placeholder="Last Name" value="<?= @$last_name ?>" required>
                                    <label for="last_name">Last Name</label>
                                    <span class="error_span text-danger"><?= @$message['last_name'] ?></span><br>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">                            
                                    <input type="email" class="form-control border border-1 border-dark-subtle" name="email" id="email" placeholder="Email" value="<?= @$email ?>" required>
                                    <label for="email">Email Address</label>
                                    <span class="error_span text-danger"><?= @$message['email'] ?></span><br>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">                            
                                    <input type="text" class="form-control border border-1 border-dark-subtle" name="nic" id="nic" placeholder="National Identity Card" value="<?= @$nic ?>" required>
                                    <label for="nic">NIC Number</label>
                                    <span class="error_span text-danger"><?= @$message['nic'] ?></span><br>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control border border-1 border-dark-subtle" name="mill_name" id="mill_name" placeholder="Mill or Company Name" value="<?= @$mill_name ?>" required>
                                    <label for="mill_name">Mill / Company Name</label>
                                    <span class="error_span text-danger"><?= @$message['mill_name'] ?></span><br>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control border border-1 border-dark-subtle" name="address_line1" id="address_line1" placeholder="Address Line 1" value="<?= @$address_line1 ?>" required>
                                    <label for="address_line1">Address Line 1</label>
                                    <span class="error_span text-danger"><?= @$message['address_line1'] ?></span><br>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control border border-1 border-dark-subtle" name="address_line2" id="address_line2" placeholder="Address Line 2" value="<?= @$address_line2 ?>" required>
                                    <label for="address_line2">Address Line 2</label>
                                    <span class="error_span text-danger"><?= @$message['address_line2'] ?></span><br>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">                                    
                                    <input type="text" class="form-control border border-1 border-dark-subtle" name="city" id="city" placeholder="City" value="<?= @$city ?>" required>
                                    <label for="city">City</label>
                                    <span class="error_span text-danger"><?= @$message['city'] ?></span><br>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <?php
                                    $sql = "SELECT * FROM districts";
                                    $result = $db->query($sql);
                                    ?>                                    
                                    <select name="district" id="district" class="form-select border border-1 border-dark-subtle">
                                        <option value="">Select Your District</option>
                                        <?php
                                        while ($row = $result->fetch_assoc()) {
                                            ?>
                                            <option value="<?= $row['Id'] ?>" <?= @$district == $row['Id'] ? 'selected' : '' ?>> <?= $row['Name'] ?> </option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <label for="district">District</label>
                                    <span class="error_span text-danger mt-4"><?= @$message['district'] ?></span><br>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">                                                                
                                    <input type="text" class="form-control border border-1 border-dark-subtle" name="mobile_no" id="mobile_no" placeholder="Mobile Number" value="<?= @$mobile_no ?>" required>
                                    <label for="mobile_no">Mobile Number (+94)</label>
                                    <span class="error_span text-danger"><?= @$message['mobile_no'] ?></span><br>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">                                
                                    <input type="text" class="form-control border border-1 border-dark-subtle" name="telno" id="telno" placeholder="Phone Number (Mill)" value="<?= @$telno ?>" required>
                                    <label for="telno">Telephone - Mill (+94)</label>
                                    <span class="error_span text-danger"><?= @$message['telno'] ?></span><br>
                                </div>
                            </div>                            
                        </div>
                        <div class="form-group mt-3">
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
                        <!--$UserId must be passed when submitting the form by POST method to use the variable in the sql queries-->
                        <input type="hidden" name="UserId" value="<?= $UserId ?>">
                        <input type="hidden" name="operate" value="change_details">
                        <div class="text-left"><button type="submit">Update</button></div>
                    </div>
                </div>
            </form>       
        </div>
    </section>

    <section id="contact" class="contact">
        <div class="container" data-aos="fade-up">
            <div class="row">
                <div class="col-12">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Change Password</h3>
                        </div> 
                        <!--form to update the password-->
                        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating">                            
                                            <input type="password" class="form-control border border-1 border-dark-subtle" name="old_password" id="old_password" placeholder="Your Existing Password" value="<?= @$old_password ?>" required>
                                            <label for="Password">Existing Password</label>
                                            <span class="error_span text-danger"><?= @$message['old_password'] ?></span><br>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating">                            
                                            <input type="password" class="form-control border border-1 border-dark-subtle" name="new_password" id="new_password" placeholder="Your New Password" value="<?= @$new_password ?>" required>
                                            <label for="new_password">New Password</label>
                                            <span class="error_span text-danger"><?= @$message['new_password'] ?></span><br>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">                            
                                            <input type="password" class="form-control border border-1 border-dark-subtle" name="confirm_password" id="confirm_password" placeholder="Confirm your new Password" required>
                                            <label for="confirm_password">Confirm Your New Password</label>
                                            <span class="error_span text-danger"><?= @$message['confirm_password'] ?></span><br>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <!--$UserId must be passed when submitting the form by POST method to use the variable in the sql queries-->
                                <input type="hidden" name="UserId" value="<?= $UserId ?>">
                                <input type="hidden" name="operate" value="change_password">
                                <button type="submit" class="btn btn-dark float-right">Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>




</main>

<?php
include 'dashboard_footer.php';
ob_end_flush();
?>

