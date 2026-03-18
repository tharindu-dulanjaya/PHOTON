<?php

include '../../function.php';
$db = dbConn();

// decode the JSON data received from the calendar
$events = json_decode($_POST['events'], true);

//consider only the first record to retrieve data of all columns
$row = $events[0];

$ticketId = $row['ticketId'];
$millName = $row['millName'];
$description = $row['issue'];
$start = $row['start'];
$backgroundColor = $row['backgroundColor'];
$borderColor = $row['borderColor'];

$sql = "INSERT INTO service_schedule (ticketId, millName, description, start, backgroundColor, borderColor) VALUES ('$ticketId','$millName','$description','$start','$backgroundColor','$borderColor')";
$db->query($sql);

// loop the data array to get technician id and the name
foreach ($events as $event) {

    $title = $event['title']; // ID and the name of the technician    
    $tecPerson = explode("-", $title); // to separate the text from '-' 
    $empId = $tecPerson[0]; // first element
    $empname = $tecPerson[1]; // second element
    $ticketId = $event['ticketId'];

    $sql = "INSERT INTO ticket_technicians (TicketId, EmployeeId, EmployeeName) VALUES ('$ticketId','$empId','$empname')";
    $db->query($sql);
}

// update ticket status as scheduled
$sql = "UPDATE tickets SET TicketStatus = '2' WHERE TicketId = '$ticketId'";
$db->query($sql);

// return the ticket ID as JSON response
echo json_encode(['ticketId' => $ticketId]);



