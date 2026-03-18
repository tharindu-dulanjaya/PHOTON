x<?php
ob_start();
session_start();
include_once '../init.php';

$db = dbConn();

extract($_GET); //to get the ticket id from url
extract($_POST);

// ticketid cannot be empty for neither get or post
if (!empty($ticketid)) {
    $sql = "SELECT "
            . "tickets.TicketId,"
            . "tickets.TicketNo,"
            . "tickets.OpenedDate, "
            . "tickets.TicketStatus, "
            . "ticket_status.Ticket_status, "
            . "tickets.ModelNo, "
            . "tickets.SerialNo, "
            . "tickets.TicketStatus, "
            . "tickets.Description, "
            . "tickets.MachineCatId, "
            . "tickets.CompletedOn, "
            . "tickets.TechComments, "
            . "tickets.RepairPayment, "
            . "titles.Title, "
            . "customers.MillName, "
            . "customers.TelNo, "
            . "users.FirstName, "
            . "users.LastName, "
            . "users.City, "
            . "users.AddressLine1, "
            . "users.AddressLine2, "
            . "users.MobileNo, "
            . "item_category.category_name, "
            . "districts.Name, "
            . "common_machine_issues.Issue, "
            . "service_schedule.start, "
            . "orders.order_date "
            . "FROM tickets "
            . "INNER JOIN customers ON tickets.CustomerId = customers.CustomerId "
            . "INNER JOIN users ON users.UserId = customers.UserId "
            . "LEFT JOIN orders ON orders.id=tickets.OrderId "
            . "INNER JOIN titles ON users.TitleId = titles.Id "
            . "INNER JOIN districts ON districts.Id = users.DistrictId "
            . "INNER JOIN ticket_status ON ticket_status.Id=tickets.TicketStatus "
            . "INNER JOIN item_category ON tickets.MachineCatId = item_category.id "
            . "INNER JOIN common_machine_issues ON tickets.CommonIssueId = common_machine_issues.Id "
            . "INNER JOIN service_schedule ON service_schedule.ticketId = tickets.TicketId "
            . "WHERE tickets.TicketId='$ticketid'";

    $result = $db->query($sql);
    $row = $result->fetch_assoc();

    $ticket_id = $row['TicketId'];
    $ticket_no = $row['TicketNo'];
    $opened_date = $row['OpenedDate'];
    $title = $row['Title'];
    $first_name = $row['FirstName'];
    $last_name = $row['LastName'];
    $mill_name = $row['MillName'];
    $tel_no = $row['TelNo'];
    $mobile_no = $row['MobileNo'];
    $address_line1 = $row['AddressLine1'];
    $address_line2 = $row['AddressLine2'];
    $city = $row['City'];
    $district = $row['Name'];
    $machine_cat_id = $row['MachineCatId'];
    $machine_cat = $row['category_name'];
    $model_no = $row['ModelNo'];
    $serial_no = $row['SerialNo'];
    $purchase_date = $row['order_date'];
    $issue = $row['Issue'];
    $comments = $row['Description'];
    $service_date = $row['start'];
    $TicketStatusId = $row['TicketStatus'];
    $TicketStatus = $row['Ticket_status'];
    $completed_date = $row['CompletedOn'];
    $technician_comments = $row['TechComments'];
    $ticket_payment = $row['RepairPayment'];
}

// replace technicians
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'replace') {
    $sql = "UPDATE ticket_technicians SET EmployeeId='$assign_new', EmployeeName='$newEmpName' WHERE EmployeeId='$instead_of' AND TicketId='$ticket_id'";
    $db->query($sql);
}

// add new technicians
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'add') {
    $sql = "INSERT INTO ticket_technicians(TicketId, EmployeeId, EmployeeName) VALUES ('$ticket_id','$add_new','$addTechName')";
    $db->query($sql);
}

// when re-schedule button clicked
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 're_schedule') {

    $message = array();
    if (empty($reschedule_date)) {
        $message['reschedule_date'] = "Please select the re-scheduled date!";
    }
    if (empty($message)) {
        $sql = "UPDATE service_schedule SET start='$reschedule_date' WHERE ticketId='$ticket_id';";
        $db->query($sql);

        // change ticket status as 'rescheduled'
        $sql = "UPDATE tickets SET TicketStatus='3' WHERE ticketId='$ticket_id';";
        $db->query($sql);

        // redirect to load the updated the date in the view
        header("Location: ticket_details.php?ticketid=$ticket_id");
    }
}

// when cancel button is clicked
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'cancel') {
    // change ticket status as 'cancelled'
    $sql = "UPDATE tickets SET TicketStatus='6' WHERE ticketId='$ticket_id';";
    $db->query($sql);
    // redirect to load the updated status
    header("Location: ticket_details.php?ticketid=$ticket_id");
}

// when technician click 'mark ticket as completed' button 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'completed') {
    $today = date("Y-m-d");
    // change ticket status as 'completed'
    
    $sql = "UPDATE tickets SET TicketStatus='5',RepairPayment='$repair_payment',TechComments='$tech_comments',CompletedBy='$completed_by',CompletedOn='$today' WHERE ticketId='$ticket_id';";
    $result = $db->query($sql);
    if ($result) {
        echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Marked as completed!',
                    html: '',
                    showCloseButton: false,
                    showConfirmButton: false,
                    timer: 3000
                    }).then(function() {
                window.location.href = 'ticket_details.php?ticketid=$ticket_id';
            });
                </script>";
    }
}

// manager marks the ticket payment status as 'received' 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'paymentReceived') {
    $sql = "UPDATE tickets SET TicketPaymentStatus='2' WHERE ticketId='$ticket_id';";
    $result = $db->query($sql);
    if ($result) {
        echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Payment Received!',
                    html: '',
                    showCloseButton: false,
                    showConfirmButton: false,
                    timer: 3000
                    }).then(function() {
                window.location.href = 'scheduled_services.php';
            });
                </script>";
    }
}

$link = "Ticket Details of # " . $ticket_no . ' ';
$breadcrumb_item = "Ticket";
$breadcrumb_item_active = "Details";
?> 

<!-- re-schedule Modal--> 
<div class="modal fade" id="reschedule" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title fs-5">Re-Schedule Service Date</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                <div class="modal-body">
                    <label for="reschedule_date" class="form-label">Select New Date</label>
                    <input type="date" min="<?= date('Y-m-d'); ?>" class="form-control" id="reschedule_date" name="reschedule_date">
                    <span class="error_span text-danger"><?= @$message['reschedule_date'] ?></span><br>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="ticketid" value="<?= $ticket_id ?>">
                    <input type="hidden" name="action" value="re_schedule">
                    <button type="submit" class="btn btn-warning">Re-Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- cancel Modal--> 
<div class="modal fade" id="cancel" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title fs-5">Cancel Ticket</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                <div class="modal-body">
                    <label for="reschedule_date" class="form-label text-danger">Are you sure you want to cancel this Ticket?</label><br>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="ticketid" value="<?= $ticket_id ?>">
                    <input type="hidden" name="action" value="cancel">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="submit" class="btn btn-danger">Yes, Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>  

<?php
if ($_SESSION['DESIGNATIONID'] == 6 || $_SESSION['DESIGNATIONID'] == 1) { // technical manager & admin
    ?>
    <a href="<?= SYS_URL ?>tickets/scheduled_services.php" class="btn btn-outline-dark mb-2"><i class="fas fa-arrow-left "> </i> View All</a>
    <?php
} elseif ($_SESSION['DESIGNATIONID'] == 7) { // technician
    ?>
    <a href="<?= SYS_URL ?>dashboard.php" class="btn btn-outline-dark mb-2"><i class="fas fa-arrow-left "> </i> Go Back</a>
    <?php
}
?>
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg bg-dark">
                <h3 class="card-title">Ticket Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <label for="ticket_no" class="col-sm-6 col-form-label">Ticket Status</label>
                    <div class="col-sm-6">
                        <span class="
                        <?php
                        if ($row['TicketStatus'] == '2') {
                            echo 'badge badge-info';
                        } elseif ($row['TicketStatus'] == '3') {
                            echo 'badge badge-warning';
                        } elseif ($row['TicketStatus'] == '4') {
                            echo 'badge badge-primary';
                        } elseif ($row['TicketStatus'] == '5') {
                            echo 'badge badge-success';
                        } else {
                            echo 'badge badge-danger';
                        }
                        ?>"><?= $TicketStatus ?></span>
                    </div>
                </div>
                <div class="row">
                    <label for="ticket_no" class="col-sm-6 col-form-label">Ticket Number</label>
                    <div class="col-sm-6">
                        <!--ticket_id is hidden to send to service_schedule table using javascript-->
                        <input type="hidden" name="ticket_id" id="ticket_id" value="<?= @$ticket_id ?>" readonly>
                        <input type="text" name="ticket_no" id="ticket_no" class="form-control-plaintext" value="<?= @$ticket_no ?>" readonly>
                    </div>
                </div>
                <div class="row">
                    <label for="opened_date" class="col-sm-6 col-form-label">Created On</label>
                    <div class="col-sm-6">
                        <input type="text" name="opened_date" id="opened_date" class="form-control-plaintext" value="<?= @$opened_date ?>" readonly>
                    </div>
                </div>
                <?php
                // display only if the ticket is completed
                if ($TicketStatusId == '5') {
                    ?>
                    <div class="row">
                        <label for="completed_date" class="col-sm-6 col-form-label text-success">Completed On</label>
                        <div class="col-sm-6">
                            <input type="text" name="completed_date" id="completed_date" class="form-control-plaintext text-success" value="<?= @$completed_date ?>" readonly>
                        </div>
                    </div>
                    <?php
                }
                ?>

            </div>
        </div>
        <div class="card">
            <div class="card-header bg bg-dark">
                <h3 class="card-title">Machine Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <label for="machine_cat" class="col-sm-5 col-form-label">Machine Category</label>
                    <div class="col-sm-7">
                        <input type="text" name="machine_cat" id="machine_cat" class="form-control-plaintext" value="<?= @$machine_cat ?>" readonly>
                    </div>
                </div>
                <div class="row">
                    <label for="model_no" class="col-sm-5 col-form-label">Model Number</label>
                    <div class="col-sm-7">
                        <input type="text" name="model_no" id="model_no" class="form-control-plaintext" value="<?= @$model_no ?>" readonly>
                    </div>
                </div>
                <div class="row">
                    <label for="serial_no" class="col-sm-5 col-form-label">Serial Number</label>
                    <div class="col-sm-7">
                        <input type="text" name="serial_no" id="serial_no" class="form-control-plaintext" value="<?= @$serial_no ?>" readonly>
                    </div>
                </div>
                <div class="row">
                    <label for="purchase_date" class="col-sm-5 col-form-label">Purchased Date</label>
                    <div class="col-sm-7">
                        <input type="text" name="purchase_date" id="purchase_date" class="form-control-plaintext" value="<?= @$purchase_date ?>" readonly>
                    </div>
                </div>
                <div class="row">
                    <label for="issue" class="col-sm-5 col-form-label">Issue</label>
                    <div class="col-sm-7">
                        <input type="text" name="issue" id="issue" class="form-control-plaintext text-danger" value="<?= @$issue ?>" readonly>
                    </div>
                </div>
                <div class="row">
                    <label for="comments" class="col-sm-5 col-form-label">Comments</label>
                    <div class="col-sm-7">
                        <textarea type="text" name="comments" id="comments" class="form-control-plaintext"readonly><?= @$comments ?></textarea>
                    </div>
                </div>                
            </div>
        </div>
        <div class="card">
            <div class="card-header bg bg-dark">
                <h3 class="card-title">Customer Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <label for="name" class="col-sm-5 col-form-label">Customer Name</label>
                    <div class="col-sm-7">
                        <input type="text" name="name" id="name" class="form-control-plaintext" value="<?= @$title ?> <?= @$first_name ?> <?= @$last_name ?>" readonly>
                    </div>
                </div>
                <div class="row">
                    <label for="mobile_no" class="col-sm-5 col-form-label">Mobile Number</label>
                    <div class="col-sm-7">
                        <input type="text" name="mobile_no" id="mobile_no" class="form-control-plaintext" value="<?= @$mobile_no ?>" readonly>
                    </div>
                </div>
                <div class="row">
                    <label for="mill_name" class="col-sm-5 col-form-label">Mill Name</label>
                    <div class="col-sm-7">
                        <input type="text" name="mill_name" id="mill_name" class="form-control-plaintext" value="<?= @$mill_name ?>" readonly>
                    </div>
                </div>
                <div class="row">
                    <label for="address" class="col-sm-5 col-form-label">Address</label>
                    <div class="col-sm-7">
                        <input type="text" name="address" id="address" class="form-control-plaintext" value="<?= @$address_line1 ?>, <?= @$address_line2 ?>, <?= @$city ?>" readonly>
                    </div>
                </div>
                <div class="row">
                    <label for="district" class="col-sm-5 col-form-label">District</label>
                    <div class="col-sm-7">
                        <input type="text" name="district" id="district" class="form-control-plaintext" value="<?= @$district ?>" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-8">
        <div class="sticky-top mb-3">
            <div class="card">
                <div class="card-header bg bg-primary">
                    <h3 class="card-title">Service Schedule</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <label for="scheduled_on" class="col-sm-6 col-form-label">Scheduled On</label>
                            <div class="col-sm-6">
                                <input type="text" name="scheduled_on" id="scheduled_on" class="form-control-plaintext" value="<?= $service_date ?>">
                                <?php
                                // cancel & reschedule buttons only visible to manager and admin
                                if ($_SESSION['DESIGNATIONID'] == 6 || $_SESSION['DESIGNATIONID'] == 1) { // 6-technical manager
                                    ?>
                                    <button type="button" class="btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#reschedule" data-olddate="<?= $service_date ?>" <?= $TicketStatusId >= '5' ? 'disabled' : '' ?>><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancel"  <?= $TicketStatusId >= '5' ? 'disabled' : '' ?>><i class="fas fa-trash"></i> &nbsp;&nbsp; Cancel</button>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>                        
                        <div class="col-6">
                            <label for="ticket_no" class="col-sm-6 col-form-label">Assigned Technicians</label>
                            <div class="col-sm-6">
                                <?php
                                $sql = "SELECT EmployeeName FROM ticket_technicians WHERE TicketId = '$ticket_id'";
                                $result = $db->query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <span class="badge badge-success" value=""><?= $row['EmployeeName'] ?></span>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-6">
                            <?php
                            // only visible to technician
                            if ($_SESSION['DESIGNATIONID'] == 7) { // 7-technician
                                ?>
                                <div>
                                    <form id="markAsCompleted" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                                        <div class="m-2">
                                            <label for="scheduled_on" >Collected Payments (Rs.)</label>
                                            <input type="text" name="repair_payment" id="repair_payment" class="form-control" value="Free of charge">
                                        </div>
                                        <div class="m-2">
                                            <label for="scheduled_on" >Technician Comments</label>
                                            <textarea type="text" name="tech_comments" id="tech_comments" class="form-control" ></textarea>
                                        </div>
                                        <div class="m-2">
                                            <?php
                                            $sql = "SELECT EmployeeId, EmployeeName FROM ticket_technicians WHERE TicketId = '$ticket_id'";
                                            $result = $db->query($sql);
                                            ?>
                                            <label for="completed_by" class="col-form-label">Completed By:</label>
                                            <select class="form-control" id="completed_by" name="completed_by">
                                                <option value="">--</option>
                                                <?php while ($row = $result->fetch_assoc()) { ?>
                                                    <option value="<?= $row['EmployeeName'] ?>"><?= $row['EmployeeName'] ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <input type="hidden" name="ticketid" value="<?= $ticket_id ?>">
                                        <input type="hidden" name="action" value="completed">
                                        <!--should be enabled only after 'working right now' status (4)-->
                                        <button type="submit" class="btn btn-primary float-right m-2" <?= $TicketStatusId == '5' ? '' : 'disabled' ?>>Mark as completed &nbsp; <i class="fas fa-check"></i></button>
                                    </form>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>

                    <?php
                    // section only visible to manager & admin
                    if ($_SESSION['DESIGNATIONID'] == 6 || $_SESSION['DESIGNATIONID'] == 1) { // technical manager
                        ?>
                        <div class="row mt-5">
                            <div class="col-5">
                                <form id="replaceTechs" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                                    <h5>Replace Technicians</h5>
                                    <div>
                                        <?php
                                        $sql = "SELECT EmployeeId, EmployeeName FROM ticket_technicians WHERE TicketId = '$ticket_id'";
                                        $result = $db->query($sql);
                                        ?>
                                        <label for="instead_of" class="col-form-label">Instead of:</label>
                                        <!--if ticket is already 'completed' or 'cancelled' cannot allow to edit. therefore disabled-->
                                        <select class="form-control" id="instead_of" name="instead_of" <?= $TicketStatusId >= '5' ? 'disabled' : '' ?>>
                                            <option value="">--</option>
                                            <?php while ($row = $result->fetch_assoc()) { ?>
                                                <option value="<?= $row['EmployeeId'] ?>"><?= $row['EmployeeName'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div>                            
                                        <?php
                                        // display all the technicias except the currently assigned members
                                        $sql2 = "SELECT e.EmployeeId, u.FirstName, u.LastName FROM technicians t "
                                                . "INNER JOIN employees e ON e.EmployeeId = t.EmpId "
                                                . "INNER JOIN users u ON u.UserId = e.UserId "
                                                . "WHERE EmployeeId "
                                                . "NOT IN (SELECT EmployeeId FROM ticket_technicians WHERE TicketId = '$ticket_id')";
                                        $result2 = $db->query($sql2);
                                        ?>
                                        <label for="assign_new" class="col-form-label">Assign:</label>
                                        <!--on change, update the selected name in the hidden field using js-->
                                        <select class="form-control" id="assign_new" name="assign_new" <?= $TicketStatusId >= '5' ? 'disabled' : '' ?>>
                                            <option value="">--</option>
                                            <?php
                                            while ($row2 = $result2->fetch_assoc()) {
                                                ?>
                                                <!-- 'data-name' attribute to store employee full name-->
                                                <option value="<?= $row2['EmployeeId'] ?>" data-name="<?= $row2['FirstName'] ?> <?= $row2['LastName'] ?>" > <?= $row2['FirstName'] ?> <?= $row2['LastName'] ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                        <!--send new employee name in a hidden field to insert in to database. set using js-->
                                        <input type="hidden" name="newEmpName" id="newEmpName" value="">
                                    </div>
                                    <div class="mt-3">
                                        <input type="hidden" name="action" value="replace">
                                        <input type="hidden" name="ticketid" value="<?= $ticket_id ?>">
                                        <button type="submit" class="btn btn-warning" <?= $TicketStatusId >= '5' ? 'disabled' : '' ?>>Replace Now</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-1">
                            </div>
                            <div class="col-6">
                                <form id="addTechs" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                                    <h5>Add Technicians</h5>
                                    <div>                            
                                        <?php
                                        // display all the technicias except the currently assigned members
                                        $sql = "SELECT e.EmployeeId, u.FirstName, u.LastName FROM technicians t "
                                                . "INNER JOIN employees e ON e.EmployeeId = t.EmpId "
                                                . "INNER JOIN users u ON u.UserId = e.UserId "
                                                . "WHERE EmployeeId "
                                                . "NOT IN (SELECT EmployeeId FROM ticket_technicians WHERE TicketId = '$ticket_id')";
                                        $result = $db->query($sql);
                                        ?>
                                        <label for="add_new" class="col-form-label">New:</label>
                                        <!--on change, update the selected name in the hidden field using js-->
                                        <select class="form-control" id="add_new" name="add_new" <?= $TicketStatusId >= '5' ? 'disabled' : '' ?>>
                                            <option value="">--</option>
                                            <?php
                                            while ($row = $result->fetch_assoc()) {
                                                ?>
                                                <!-- 'data-newTech' attribute to store employee full name-->
                                                <option value="<?= $row['EmployeeId'] ?>" data-name="<?= $row['FirstName'] ?> <?= $row['LastName'] ?>" > <?= $row['FirstName'] ?> <?= $row['LastName'] ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                        <!--send new employee name in a hidden field to insert in to database. set using js-->
                                        <input type="hidden" name="addTechName" id="addTechName" value="">
                                    </div>
                                    <div class="mt-3">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="ticketid" value="<?= $ticket_id ?>">
                                        <button type="submit" class="btn btn-info" <?= $TicketStatusId >= '5' ? 'disabled' : '' ?>>Add Technician</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                        <?php
                    }
                    // not visible to technician
                    if ($_SESSION['DESIGNATIONID'] != 7) {
                        ?>
                        <!--see the tech comments and collected repair payment-->
                        <div class="col-6 mt-5">
                            <div>                                    
                                <div class="m-2">
                                    <label>Technician Comments</label>
                                    <textarea type="text" class="form-control-plaintext" ><?= $technician_comments ?></textarea>
                                </div>
                                <div class="m-2">
                                    <label>Collected Payments (Rs.)</label>
                                    <input type="text" class="form-control-plaintext" value="<?= $ticket_payment ?>">
                                </div>
                                <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                                    <input type="hidden" name="ticketid" value="<?= $ticket_id ?>">
                                    <input type="hidden" name="action" value="paymentReceived">
                                    <!--should be enabled only after 'completed' status (5)-->
                                    <button type="submit" class="btn btn-primary float-right m-2" <?= $TicketStatusId == '5' ? '' : 'disabled' ?>>Payment Received &nbsp; <i class="fas fa-check"></i></button>
                                </form>
                            </div>
                        </div>
                        <?php
                    }
                    ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../layouts.php';
?>

<script>

    $(document).ready(function () {

        // reschedule modal
        $('#reschedule').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var old_date = button.data('olddate');

            var modal = $(this);
            modal.find('.modal-body input#reschedule_date').val(old_date);
        });

        // replace technician, update the #newEmpName hidden field value
        $('#assign_new').change(function () {
            var selectedOption = $(this).find('option:selected');
            var empName = selectedOption.data('name');
            $('#newEmpName').val(empName);
        });

        // add new technician, update the #addTechName hidden field value
        $('#add_new').change(function () {
            var selectedOption = $(this).find('option:selected');
            var empNewName = selectedOption.data('name');
            $('#addTechName').val(empNewName);
        });

        // checks if all the fields in replaceTechs form are filled when submitting
        $('#replaceTechs').submit(function (event) {

            // check if 'Instead of' is not empty
            if ($('#instead_of').val().trim() === '') {
                Swal.fire({
                    icon: 'info',
                    html: 'Instead of field cannot be empty!',
                    showConfirmButton: false,
                    timer: 2500
                });
                event.preventDefault(); // prevent the form submission 
                return false; // stop further code execution
            }
            // check if 'Assign' is not empty
            if ($('#assign_new').val().trim() === '') {
                Swal.fire({
                    icon: 'info',
                    html: 'Assign field cannot be empty!',
                    showConfirmButton: false,
                    timer: 2500
                });
                event.preventDefault(); // prevent the form submission 
                return false; // stop further code execution
            }
        });

        $('#addTechs').submit(function (event) {

            // check if 'Instead of' is not empty
            if ($('#add_new').val().trim() === '') {
                Swal.fire({
                    icon: 'info',
                    html: 'Select the New Technician name!',
                    showConfirmButton: false,
                    timer: 2500
                });
                event.preventDefault(); // prevent the form submission 
                return false; // stop further code execution
            }
        });

        // when submitting mark as completed button
        $('#markAsCompleted').submit(function (event) {
            // Validate the repair_payment field
            if ($('#repair_payment').val().trim() === '') {
                Swal.fire({
                    icon: 'info',
                    title: '',
                    html: '<h5>Please enter the collected payment amount or specify "Free of charge".</h5>',
                    showConfirmButton: false,
                    showCloseButton: false
                });
                event.preventDefault();  // prevent the form submission 
                return; // prevent executing below code
            }

            // Validate the tech_comments field
            if ($('#tech_comments').val().trim() === '') {
                Swal.fire({
                    icon: 'warning',
                    title: '',
                    html: '<h5>Please enter some comments of the service job.</h5>',
                    showConfirmButton: false,
                    showCloseButton: false
                });
                event.preventDefault();  // prevent the form submission 
                return; // prevent executing below code
            }
        });


    });
</script>

