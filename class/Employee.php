<?php

class Employee extends User {
    
    public function __construct() {
        parent::__construct();  // Call the parent constructor to initialize the database connection
    }    
    public function saveEmployee($title=null,$first_name=null,$last_name=null,$email=null,$nic=null,$address_line1=null,$address_line2=null,$city=null,$mobile_no=null,$gender=null,$district=null,$user_name=null,$pw=null,$app_date=null, $designation_id=null, $department_id=null, $UserId=null) {
        // Insert user and get the UserId
        $UserId = parent::save($title,$first_name,$last_name,$email,$nic,$address_line1,$address_line2,$city,$mobile_no,$gender,$district,$user_name,$pw);

        // Insert into employee table
        $sql = "INSERT INTO employees(AppointmentDate,DesignationId,DepartmentId,UserId) VALUES ('$app_date','$designation_id','$department_id','$UserId')";
        $this->db->query($sql);
    }
    
}
