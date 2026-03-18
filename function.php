<?php
// Create Database Connection
function dbConn() {
    $server = 'localhost';
    $username = 'root';
    $password = '';
    $db = 'photon';

    $conn = new mysqli($server, $username, $password, $db);

    if ($conn->connect_error) {
        die("Database ERROR " . $conn->connect_error);
    } else {
        return $conn;
    }
}

// Data Clean function
function dataClean($data = null) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);

    return $data;
}

// Check permission for a url
function checkPermission($current_url = null, $userid = null) {
    $parsed_url = parse_url($current_url); // Get the file name and folder name from the current URL dynamically
    $path = $parsed_url['path'];
    $file_name = basename($path, '.php'); // Get filename without extension
    $folder_name = basename(dirname($path)); // Get the folder name

    $db = dbConn();
    $sql = "SELECT * FROM user_modules um "
            . "INNER JOIN modules  m ON m.Id=um.ModuleId "
            . "WHERE um.UserId='$userid' AND m.Path='$folder_name' AND m.File='$file_name'";

    $result = $db->query($sql);

    if ($result->num_rows <= 0) {
        return false;
    } else {
        return true;
    }
}

// check access to frontend and backend
function checkAccess($user_type = null) { // user_type may be employee or customer
    //If a session is not already created, start a session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    // take the user id from the session
    $user_id = $_SESSION['USERID'];
    $db = dbConn();
    // check if the logged in UserType matches with the allowed user type
    $sql = "SELECT * FROM users WHERE UserId='$user_id' AND UserType='$user_type' ";

    $result = $db->query($sql);

    if ($result->num_rows <= 0) { // less than or equal to 0
        header("Location:../unauthorized.php");
        return false;
    } else {
        return true;
    }
}

// check privileges for the CRUD operations
function checkprivilege($module_id = null) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $user_id = $_SESSION['USERID'];
    $db = dbConn();
    $sql = "SELECT * FROM user_modules u WHERE u.UserId='$user_id' AND u.ModuleId='$module_id'";
    $result = $db->query($sql);
    $row = $result->fetch_assoc();
    if ($result->num_rows <= 0) {
        header("Location:../unauthorized.php");
        return false;
    } else {
        return $row;
    }
}

// Single image & file upload
function uploadFile($file, $location) {
    $message = array();
    // ['name'] is an attribute of the array(more example: name,full_path,tmp_name,size,error,type..)
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    // Work out the file extension
    $file_ext = explode('.', $file_name);
    $file_ext = strtolower(end($file_ext));
    $allowed_ext = array('png', 'jpg', 'gif', 'jpeg', 'pdf');
    if (in_array($file_ext, $allowed_ext)) { // check if the uploaded image extension is within the allowed extensions
        if ($file_error === 0) {
            if ($file_size <= 5097152) {
                $file_name = uniqid('', true) . '.' . $file_ext; //create new unique file name
                $file_destination = $location . '/' . $file_name; //file destination
                move_uploaded_file($file_tmp, $file_destination); //moves the file from the temp location to the new destination
                $message['upload'] = true;
                $message['file'] = $file_name;
            } else { //validation if exeeds the maximum file size
                $message['upload'] = false;
                $message['error_file'] = "The file size is invalid for $file_name";
            }
        } else { //validation if file has error/corrupted
            $message['upload'] = false;
            $message['error_file'] = "Error occurred while uploading $file_name";
        }
    } else { //validation if wrong file type
        $message['upload'] = false;
        $message['error_file'] = "Invalid file type selected!";
    }

    return $message;
}

// Multiple image & file uploads
function uploadFiles($files, $location) {
    $messages = array();

    // 'name' is an attribute of the array(more example: name,full_path,tmp_name,size,error,type..)  
    // $key is 0,1,2, .. (index of the array)
    // $filename receives the values of above indexes
    foreach ($files['name'] as $key => $filename) {
        $filetmp = $files['tmp_name'][$key]; // assign the value of tmp_name of each index
        $filesize = $files['size'][$key];
        $fileerror = $files['error'][$key];

        $file_ext = explode('.', $filename); // breaks the string in to two arrays by the given point
        $file_ext = strtolower(end($file_ext));

        $allowed_ext = array('pdf', 'png', 'jpg', 'gif', 'jpeg'); //allowed extensions

        if (in_array($file_ext, $allowed_ext)) { // check if the uploaded image extension is within the allowed extensions
            if ($fileerror === 0) {
                if ($filesize <= 5097152) {
                    $file_name = uniqid('', true) . '.' . $file_ext; //create new unique file name
                    $file_destination = $location . '/' . $file_name; //file destination
                    move_uploaded_file($filetmp, $file_destination); //moves the file from the temp location to the new destination
                    $messages[$key]['upload'] = true;
                    $messages[$key]['file'] = $file_name;
                } else { //validation if exeeds the maximum file size
                    $messages[$key]['upload'] = false;
                    $messages[$key]['size'] = "The file size is invalid for $filename";
                }
            } else { //validation if file has error/corrupted
                $messages[$key]['upload'] = false;
                $messages[$key]['uploading'] = "Error occurred while uploading $filename";
            }
        } else { //validation if wrong file type
            $messages[$key]['upload'] = false;
            $messages[$key]['type'] = "Invalid file type for $filename";
        }
    }
    return $messages;
}

// validate NIC number
function validateNIC($nic) {
    // determine the length of the NIC
    $length = strlen($nic);

    // check if the nic field is empty
    if ($length == 0) {
        return "Please enter your NIC number";
    }
    // check for old NIC format
    elseif ($length == 10) {
        $firstPart = substr($nic, 0, 9);
        // use strtoupper() function to convert v to V and x to X
        // -1 means the final character starting from the end
        $lastChar = strtoupper(substr($nic, -1));

        // ctype_digit() is used to check if all characters are numeric
        // check if the first part is numeric and the last character is 'V' or 'X'
        if (ctype_digit($firstPart) && ($lastChar == 'V' || $lastChar == 'X')) {
            return ''; // Valid old NIC format
        } else {
            return "Invalid NIC format";
        }
    }
    // check for new NIC format
    elseif ($length == 12) {
        // check if all characters are numeric
        if (ctype_digit($nic)) {
            return ''; // Valid new NIC format
        } else {
            return "Invalid NIC format.";
        }
    } else {
        return "Invalid NIC format.";
    }
}

// validate the phone number for +94 format
function validatePhoneNumber($phone) {
    // remove leading and trailing whitespace
    $phone = trim($phone);

    // determine the length of the phone number
    $length = strlen($phone);
    if ($length == 0) {
        return "Please enter Phone number";
    }
    // Check if the number starts with +94 followed by 9 digits
    // 0,3 means the first 3 characters starting from the begining
    elseif (substr($phone, 0, 3) === '+94' && $length === 12 && ctype_digit(substr($phone, 3))) {
        return ''; // Valid phone number
    } else {
        return "Invalid Phone number.";
    }
}
