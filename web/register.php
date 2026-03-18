<?php
ob_start();
include 'header.php';
include '../mail.php';
$db = dbConn();
?>
<main id="main">
    <section id="contact" class="contact">
        <div class="container" data-aos="fade-up">
            <div class="section-title">
                <h2>Customer</h2>
                <p>Register</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-7 mt-5 mt-lg-0 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="200">
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
                        $user_name = dataClean($user_name);

                        $message = array(); // errors of all other fields
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
                        if (empty($user_name)) {
                            $message['user_name'] = "User Name is required!";
                        } else {
                            //check if the username already exists or not
                            $user = new User();

                            if ($user->checkUserName($user_name)) {
                                $message['user_name'] = "This User Name already exists!! Please use another";
                            }
                        }
                        if (empty($password)) {
                            $message['password'] = "Password is required";
                        } else {
                            if (strlen($password) < 8) {
                                $message['password'] = "The password should contain atleast 8 characters";
                            }
                        }
                        if (empty($confirm_password)) {
                            $message['confirm_password'] = "Please confirm your password!";
                        } else {
                            if ($password != $confirm_password) {
                                $message['confirm_password'] = "Passwords do not match!";
                            }
                        }

                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $message['email'] = "Invalid Email Address!";
                        } else {
                            //check if the email address already exists or not
                            $sql = "SELECT * FROM users WHERE Email='$email'";
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
                            $sql = "SELECT * FROM users WHERE Nic='$nic'";
                            $result = $db->query($sql);
                            if ($result->num_rows > 0) {
                                $message['nic'] = "This NIC already exists!";
                            }
                        }                        
                        //Submit data if there are no errors
                        if (empty($message)) {
                            //Encrypt the password before submitting to database. Using Inbuilt function 'password_hash' which uses Bcrypt algorithm
                            $pw = password_hash($password, PASSWORD_DEFAULT);

                            // create a token to verify the user
                            // generates a random binary number & converts to hexa decimal
                            $token = bin2hex(random_bytes(16));

                            $sql = "INSERT INTO users(UserName, Password, UserType, TitleId, FirstName, LastName, Email, Nic, AddressLine1, AddressLine2, City, MobileNo, GenderId, DistrictId, Status, Token) "
                                    . "VALUES ('$user_name','$pw','customer','$title','$first_name','$last_name','$email','$nic','$address_line1','$address_line2','$city','$mobile_no','$gender','$district','1','$token')";
                            $db->query($sql);

                            //Get the last inserted Id
                            $user_id = $db->insert_id;
                            $reg_date = date('Y-m-d');
                            $reg_no = date('y') . date('m') . date('d') . $user_id;
                            //In order to send the reg_no to success page, we use sessions
                            $_SESSION['RNO'] = $reg_no;

                            $sql = "INSERT INTO customers(RegisteredDate, MillName, TelNo, RegNo, UserId) VALUES ('$reg_date','$mill_name','$telno','$reg_no','$user_id')";
                            $db->query($sql);

                            $msg = "<h1>SUCCESSFUL</h1>";
                            $msg .= "<h2>Congratulations!!</h2>";
                            $msg .= "<p>Your account has been successfully created.</p>";
                            $msg .= "<a href='http://localhost/photon/web/verify.php?token=$token'>Click here to verify your account</a>";
                            sendEmail($email, $first_name, "Account Verification", $msg);

                            header("Location:register_success.php");
                        }
                    }
                    ?>
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" role="form" class="php-email-form" novalidate>
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
                        <div class="form-group mt-2">
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

                        <div class="form-floating">                            
                            <input type="text" class="form-control border border-1 border-dark-subtle" name="user_name" id="user_name" placeholder="Enter your Username" value="<?= @$user_name ?>" required>
                            <label for="user_name">User Name</label>
                            <span class="error_span text-danger"><?= @$message['user_name'] ?></span><br>
                        </div>                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">                                
                                    <input type="password" class="form-control border border-1 border-dark-subtle" name="password" id="password" placeholder="Enter your Password" value="<?= @$password ?>" required>
                                    <label for="password">Password</label>
                                    <span class="error_span text-danger"><?= @$message['password'] ?></span><br>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">                                
                                    <input type="password" class="form-control border border-1 border-dark-subtle" name="confirm_password" id="confirm_password" placeholder="Confirm your Password" value="<?= @$confirm_password ?>" required>
                                    <label for="confirm_password">Confirm Your Password</label>
                                    <span class="error_span text-danger"><?= @$message['confirm_password'] ?></span><br>
                                </div>
                            </div>
                            <div class="text-center"><button type="submit">REGISTER</button></div>
                    </form>
                </div>
            </div>
        </div>
        </div>
    </section>
</main>

<?php
include 'footer.php';
ob_end_flush();
?>