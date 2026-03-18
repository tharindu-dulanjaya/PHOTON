<?php
require '../../function.php';
$db = dbConn();

// get service events from database
// alias as title is required. to acces the data from the calendar side
$sql = "SELECT t.EmployeeName AS title, s.millName, s.description, s.start FROM service_schedule s LEFT JOIN ticket_technicians t ON t.TicketId = s.ticketId";

$result = $db->query($sql);

$eventsArray = array();
if($result->num_rows > 0){
    while ($row = $result->fetch_assoc()){
        array_push($eventsArray, $row);
    }
}

//render event data in JSON format
echo json_encode($eventsArray);
