<?php
include 'dashboard_header.php'; // header has the init.php 
$db = dbConn();

extract($_GET); //to get the ticket id from url
// ticketid cannot be empty
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
            . "LEFT JOIN service_schedule ON service_schedule.ticketId = tickets.TicketId "
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
    $scheduled_date = $row['start'];
    $completed_date = $row['CompletedOn'];
}
?> 

<main id="main">
    <!--Breadcrumb section-->
    <section class="breadcrumbs">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h2>My Tickets</h2>
                <ol>
                    <li><b>Customer</b></li>
                    <li><a href="<?= WEB_URL ?>dashboard.php" style="color: #fff;">Dashboard</a></li>
                    <li>Schedule</li>
                </ol>
            </div>
        </div>
    </section>
    <section class="inner-page">
        <div class="container" data-aos="fade-up">
            <a href="<?= WEB_URL ?>all_tickets.php" class="btn btn-outline-dark mb-2"><i class="fas fa-arrow-left "> </i>Go Back</a>
            <div class="row">
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-header">
                            <h4 class="card-title">Ticket Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <label for="ticket_no" class="col-sm-5 col-form-label">Ticket Status</label>
                                <div class="col-sm-7">
                                    <span class="
                                    <?php
                                    if ($row['TicketStatus'] == '1') {
                                        echo 'badge rounded-pill text-bg-warning';
                                    } elseif ($row['TicketStatus'] == '2') {
                                        echo 'badge rounded-pill text-bg-info';
                                    } elseif ($row['TicketStatus'] == '3') {
                                        echo 'badge rounded-pill text-bg-info';
                                    } elseif ($row['TicketStatus'] == '4') {
                                        echo 'badge rounded-pill text-bg-primary';
                                    } elseif ($row['TicketStatus'] == '5') {
                                        echo 'badge rounded-pill text-bg-success';
                                    } else {
                                        echo 'badge rounded-pill text-bg-danger';
                                    }
                                    ?>"><?= $TicketStatus ?></span>
                                </div>
                            </div>
                            <div class="row">
                                <label for="ticket_no" class="col-sm-5 col-form-label">Ticket Number</label>
                                <div class="col-sm-7">
                                    <input type="text" name="ticket_no" id="ticket_no" class="form-control-plaintext" value="<?= @$ticket_no ?>" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <label for="opened_date" class="col-sm-5 col-form-label">Created On</label>
                                <div class="col-sm-7">
                                    <input type="text" name="opened_date" id="opened_date" class="form-control-plaintext" value="<?= @$opened_date ?>" readonly>
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

                            <?php
                            if ($TicketStatusId == '5') { // display only if the ticket is completed
                                ?>
                                <div class="row">
                                    <label for="completed_date" class="col-sm-5 col-form-label text-success">Completed On</label>
                                    <div class="col-sm-7">
                                        <input type="text" name="completed_date" id="completed_date" class="form-control-plaintext text-success" value="<?= @$completed_date ?>" readonly>
                                    </div>
                                </div>
                                <?php
                            } elseif ($TicketStatusId > '1' && $TicketStatusId < '6') { // if not resolved or cancelled, do not show date
                                ?>
                                <div class="row">
                                    <label for="scheduled_date" class="col-sm-5 col-form-label">Scheduled On</label>
                                    <div class="col-sm-7">
                                        <input type="text" name="scheduled_date" id="scheduled_date" class="form-control-plaintext" value="<?= @$scheduled_date ?>" readonly>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>

                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-header">
                            <h4 class="card-title">Machine Information</h4>
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
                                           
                        </div>
                    </div>        
                </div>
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-header">
                            <h4 class="card-title">Service/ Repair Team</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <label for="ticket_no" class="col-sm-6 col-form-label">Assigned Technicians</label>
                                <div class="col-sm-6">
                                    <?php
                                    $sql = "SELECT EmployeeName FROM ticket_technicians WHERE TicketId = '$ticket_id'";
                                    $result = $db->query($sql);
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            ?>
                                            <span class="badge text-bg-dark" value=""><?= $row['EmployeeName'] ?></span>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <input type="text" class="form-control-plaintext text-danger" value="Not Assigned yet!" readonly>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>

                        </div>
                    </div>        
                </div>
            </div>
        </div>
    </section>
</main>





<?php
include 'dashboard_footer.php';
?>
