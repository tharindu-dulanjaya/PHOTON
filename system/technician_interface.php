<!--included in dashboard-->
<?php
ob_start();
include_once 'init.php';
$db = dbConn();

$link = "Assigned Job Tickets";
$breadcrumb_item = "Technician";
$breadcrumb_item_active = "Schedule";

$UserId = $_SESSION['USERID'];

// get the employee id of the logged in user
$sql = "SELECT EmployeeId FROM employees WHERE UserId='$UserId'";
$result = $db->query($sql);
$row = $result->fetch_assoc();
$empId = $row['EmployeeId']; // to use in sql query

extract($_POST);

// when 'working right now' button clicked
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'working') {

    // change ticket status as 'working right now'
    $sql = "UPDATE tickets SET TicketStatus='4' WHERE ticketId='$ticket_id';";
    $result = $db->query($sql);
    if ($result) {
        echo "<script>
                Swal.fire({
                    icon: 'info',
                    title: 'Marked as working right now!',
                    html: '',
                    showCloseButton: false,
                    showConfirmButton: false,
                    timer: 3000
                    });
                </script>";
    }
}
// when 'completed' button clicked
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'completed') {

    $today = date("Y-m-d");
    // change ticket status as 'completed'
    $sql = "UPDATE tickets SET TicketStatus='5', CompletedOn='$today' WHERE ticketId='$ticket_id';";
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
                    });
                </script>";
    }
}
// when 'delivered' button clicked
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'delivered') {

    // change ticket status as 'working right now'
    $sql = "UPDATE tickets SET TicketStatus='4' WHERE ticketId='$ticket_id';";
    $db->query($sql);

    // update item delivered date
    // find the relevant order_item_issue id for this serial number
    $sno = $_POST['serial_no']; // sent from a hidden field
    $sql = "SELECT Order_Items_Issue_Id FROM issued_serial_numbers WHERE SerialNo='$sno'";
    $result = $db->query($sql);
    $row = $result->fetch_assoc();
    $order_item_issue_id = $row['Order_Items_Issue_Id'];
    
    $today = date("Y-m-d");
    $sql = "UPDATE order_items_issue SET delivered_on = '$today' WHERE id='$order_item_issue_id'";
    $db->query($sql);

    // update order status as delivered
    $sql = "UPDATE orders SET order_status = '5' WHERE id='$order_id'";
    $result = $db->query($sql);

    if ($result) {
        echo "<script>
                Swal.fire({
                    icon: 'info',
                    title: 'Marked as delivered!',
                    html: '',
                    showCloseButton: false,
                    showConfirmButton: false,
                    timer: 3000
                    });
                </script>";
    }
}
// when 'installed' button clicked
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'installed') {

    $today = date("Y-m-d");
    // change ticket status as 'completed'
    $sql = "UPDATE tickets SET TicketStatus='5', CompletedOn='$today' WHERE ticketId='$ticket_id';";
    $db->query($sql);
    
    // update installed date
    // find the relevant order_item_issue id for this serial number
    $sno = $_POST['serial_no']; // sent from a hidden field
    $sql = "SELECT Order_Items_Issue_Id FROM issued_serial_numbers WHERE SerialNo='$sno'";
    $result = $db->query($sql);
    $row = $result->fetch_assoc();
    $order_item_issue_id = $row['Order_Items_Issue_Id'];
    
    $today = date("Y-m-d");
    $sql = "UPDATE order_items_issue SET installed_on = '$today' WHERE id='$order_item_issue_id'";
    $db->query($sql);
    
    // update warranty expiry date
    $warrantyExpiry = date("Y-m-d", strtotime("+1 year"));
    $sql = "UPDATE issued_serial_numbers SET WarrantyExpiryDate = '$warrantyExpiry' WHERE SerialNo='$sno'";
    $db->query($sql);
    
    // update order status as installed
    $sql = "UPDATE orders SET order_status = '6' WHERE id='$order_id'";
    $result = $db->query($sql);

    if ($result) {
        echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Marked as installed!',
                    html: '',
                    showCloseButton: false,
                    showConfirmButton: false,
                    timer: 3000
                    });
                </script>";
    }
}
?> 
<div class="row">
    <div class="col-12">
        <div class="row">
            <?php
            // cancelled tickets(status->6) should not be shown

            $sql = "SELECT * FROM ticket_technicians tt "
                    . "INNER JOIN tickets t ON t.TicketId = tt.TicketId "
                    . "INNER JOIN service_schedule ss ON ss.ticketId = t.TicketId "
                    . "INNER JOIN ticket_status ts ON ts.Id = t.TicketStatus "
                    . "WHERE tt.EmployeeId = '$empId' AND t.TicketStatus <> 6 "
                    . "ORDER BY t.TicketStatus";
            $result = $db->query($sql);
            if ($result->num_rows > 0) { // means the technician has some schedules
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <div class="col-3">
                        <div class="card <?= $row['TicketStatus'] == '5' ? 'card-success' : 'card-warning' ?> card-outline">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-9">
                                        <h5><?= $row['start'] ?></h5>
                                    </div>
                                    <div class="col-3">
                                        <a href="<?= SYS_URL ?>tickets/ticket_details.php?ticketid=<?= $row['TicketId'] ?>" class="btn btn-sm btn-outline-dark float-right">View</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
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
                                ?>
                                      "> <?= $row['Ticket_status'] ?> </span>
                                <h4><?= $row['millName'] ?></h4>

                                <!--common issue stored in service_schedule table-->
                                <h6><b><?= $row['description'] ?></b></h6>

                                <!--described issue stored in tickets table-->
                                <p class="card-text"><?= $row['Description'] ?></p>
                            </div>
                            <div class="card-footer">
                                <p><b>Mark as:</b></p>
                                <div class="row float-right">                                    
                                    <?php
                                    if ($row['CommonIssueId'] == 1) { // means it is a machine installation
                                        ?> 
                                        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                                            <!--to execute query in POST-->
                                            <input type="hidden" name="ticket_id" value="<?= $row['TicketId'] ?>">
                                            <input type="hidden" name="action" value="delivered">
                                            <!--to update the exact order item as delivered-->
                                            <input type="hidden" name="serial_no" value="<?= $row['SerialNo'] ?>">
                                            <input type="hidden" name="order_id" value="<?= $row['OrderId'] ?>">
                                            <button type="submit" class="btn-sm btn-info mr-2" <?= $row['TicketStatus'] >= '4' ? 'disabled' : '' ?>>Delivered &nbsp;&nbsp; <i class="fas fa-truck"></i></button>
                                        </form>
                                        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                                            <input type="hidden" name="ticket_id" value="<?= $row['TicketId'] ?>">
                                            <input type="hidden" name="action" value="installed">
                                            <!--to update the exact order item as installed-->
                                            <input type="hidden" name="serial_no" value="<?= $row['SerialNo'] ?>">
                                            <input type="hidden" name="order_id" value="<?= $row['OrderId'] ?>">
                                            <!--should be enabled only after 'delivered' (4)-->
                                            <button type="submit" class="btn-sm btn-outline-primary mr-2" <?= $row['TicketStatus'] == '4' ? '' : 'disabled' ?>>Installed &nbsp;&nbsp; <i class="fas fa-check-double"></i></button>
                                        </form>
                                        <?php
                                    } else {
                                        ?>
                                        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                                            <input type="hidden" name="ticket_id" value="<?= $row['TicketId'] ?>">
                                            <input type="hidden" name="action" value="working">
                                            <button type="submit" class="btn-sm btn-outline-dark mr-2" <?= $row['TicketStatus'] >= '4' ? 'disabled' : '' ?>>Working Right Now<i class="fas fa-wrench"></i></button>
                                        </form>
<!--                                        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                                            <input type="hidden" name="ticket_id" value="<?= $row['TicketId'] ?>">
                                            <input type="hidden" name="action" value="completed">
                                            should be enabled only after 'working right now' status (4)
                                            <button type="submit" class="btn-sm btn-dark mr-2" <?= $row['TicketStatus'] == '4' ? '' : 'disabled' ?>>Completed &nbsp; <i class="fas fa-check"></i></button>
                                        </form>-->
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<script>
                        Swal.fire({
                            icon: 'info',
                            title: 'No any schedules',
                            html: '<h4>You are not assigned to any job yet!</h4>',
                            showCloseButton: false,
                            showConfirmButton: false,
                            timer: 4000
                        });
                      </script>";
            }
            ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layouts.php';
?>