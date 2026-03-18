<?php

class User {

    protected $db;

    public function __construct() {
        $this->db = dbConn();
    }

    public function checkUserName($user_name) {
        //check if the username already exists or not
        $sql = "SELECT * FROM users WHERE UserName='$user_name'";
        $result = $this->db->query($sql);
        if ($result->num_rows > 0) {
            return true; //already exists
        } else {
            return false;
        }
    }

    public function save($title = null, $first_name = null, $last_name = null, $email = null, $nic = null, $address_line1 = null, $address_line2 = null, $city = null, $mobile_no = null, $gender = null, $district = null, $user_name = null, $pw = null) {
        $sql = "INSERT INTO users(TitleId,FirstName,LastName,Email,Nic,AddressLine1,AddressLine2,City,MobileNo,GenderId,DistrictId,UserName,Password,UserType,Status) VALUES ('$title','$first_name','$last_name','$email','$nic','$address_line1','$address_line2','$city','$mobile_no','$gender','$district','$user_name','$pw','employee','1')";
        $this->db->query($sql);
        return $this->db->insert_id;
    }

    public function verify($token = null) {
        $sql = "SELECT * FROM users WHERE Token = '$token' AND IsVerified = 0";
        $result = $this->db->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $UserId = $row['UserId'];

            $sql = "UPDATE users SET IsVerified = 1, Token = null WHERE UserId = '$UserId'";
            $this->db->query($sql);
            return true;
        } else {
            return false;
        }
    }
}
