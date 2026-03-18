<?php

include_once 'init.php';

//If a session is not already created, start a session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// if a user is not logged in USERID session is not created. if so, nothing is allowed to access
if (!isset($_SESSION['USERID'])) {
    header("Location:http://localhost/photon/system/login.php");
    return;
} else {
    checkAccess('employee'); // only employees are allowed to view the system dashboard

    if ($_SESSION['DESIGNATIONID'] == 1) { // Admin
        include 'admin_interface.php';
    } elseif ($_SESSION['DESIGNATIONID'] == 2) { // Managing Director
        include 'managing_director_interface.php';
    } elseif ($_SESSION['DESIGNATIONID'] == 3) { // Receptionist
        include 'receptionist_interface.php';
    } elseif ($_SESSION['DESIGNATIONID'] == 4) { // Logistic Officer
        include 'logistic_officer_interface.php';
    } elseif ($_SESSION['DESIGNATIONID'] == 5) { // Sales Manager
        include 'sales_manager_interface.php';
    } elseif ($_SESSION['DESIGNATIONID'] == 6) { // Technical Manager
        include 'technical_manager_interface.php';
    } elseif ($_SESSION['DESIGNATIONID'] == 7) { // Technician
        include 'technician_interface.php';
    } else {
        include 'empty_interface.php';
    }
}


