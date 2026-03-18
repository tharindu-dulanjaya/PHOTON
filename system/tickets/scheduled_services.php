<?php
ob_start();
include_once '../init.php';

$db = dbConn();
$link = "Resolved Tickets";
$breadcrumb_item = "Tickets";
$breadcrumb_item_active = "Scheduled";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('7'); // module id of ticket management

extract($_POST);

// when filter button clicked
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'filter') {

    // this where part executes with the filter button clicked(POST)
    // to receive all resolved tickets
    $where = "(t.TicketStatus > '1') AND";

    // if someone filter from date, it will be submitted by the form using POST method. then the sql query will be updated with WHERE clause
    if (!empty($from_date) && !empty($to_date)) {
        $where .= " s.start BETWEEN '$from_date' AND '$to_date' AND";
    }
    if (!empty($ticket_no)) {
        $where .= " t.TicketNo LIKE '%$ticket_no%' AND";
    }
    if (!empty($customer_name)) {
        $where .= " u.FirstName LIKE '%$customer_name%' AND";
    }
    if (!empty($mill_name)) {
        $where .= " c.MillName LIKE '%$mill_name%' AND";
    }
    if (!empty($district)) {
        $where .= " u.DistrictId = '$district' AND";
    }
    if (!empty($where)) {
        $where = substr($where, 0, -3); // removes the last 3 characters (AND part)
        $where = " WHERE $where"; // final complete WHERE statement
    }
} else {
    // must required where part. this executes when the page loads without post(not filtered)
    $where = " WHERE (t.TicketStatus > '1')"; // to receive all resolved tickets
}

// when re-schedule button clicked
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'edit') {

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
    }
}

// when cancel button is clicked
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'cancel') {
    $sql = "UPDATE tickets SET TicketStatus='6' WHERE ticketId='$ticket_id';";
    $db->query($sql);
}
?> 
<div class="row">
    <div class="col-12">
        <!--Filter section-->
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <p class="d-inline-flex gap-1">
                <!--filter button-->
                <button class="btn btn-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </p>
            <div class="collapse" id="collapseFilter">
                <div class="card card-body">
                    <div class="row ">
                        <div class="col-md-3">
                            <div class="input-group mb-1">
                                <label class="input-group-text" for="from_date" >From</label>
                                <input type="date" class="form-control" id="from_date" name="from_date" value="<?= @$_POST['from_date'] ?>">
                            </div>
                            <div class="input-group">
                                <label class="input-group-text" for="to_date" >To</label>
                                <input type="date" class="form-control" id="to_date" name="to_date" value="<?= @$_POST['to_date'] ?>">
                            </div>
                        </div>
                        <div class="col-md-9">   
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <input type="text" class="form-control" id="ticket_no" name="ticket_no" placeholder="Filter By Ticket Number" value="<?= @$_POST['ticket_no'] ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Filter By Customer Name" value="<?= @$_POST['customer_name'] ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <input type="text" class="form-control" id="mill_name" name="mill_name" placeholder="Filter By Mill Name" value="<?= @$_POST['mill_name'] ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <?php
                                    $sql = "SELECT * FROM districts";
                                    $result = $db->query($sql);
                                    ?>
                                    <select class="form-control" name="district">
                                        <option value="" readonly>Filter By District</option>
                                        <?php while ($row = $result->fetch_assoc()) { ?>
                                            <option value="<?= $row['Id'] ?>" <?= @$_POST['district'] == $row['Id'] ? 'selected' : '' ?>><?= $row['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="action" value="filter">
                            <button type="submit" class="btn btn-dark float-right"><i class="fas fa-search"></i> Filter</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!-- filter part end-->

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
                            <input type="date" class="form-control" min="<?= date('Y-m-d'); ?>" id="reschedule_date" name="reschedule_date">
                            <span class="error_span text-danger"><?= @$message['reschedule_date'] ?></span><br>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" id="ticket_id" name="ticket_id">
                            <input type="hidden" name="action" value="edit">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                            <span id="mill"></span>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" id="ticket_id" name="ticket_id">
                            <input type="hidden" name="action" value="cancel">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                            <button type="submit" class="btn btn-danger">Yes, Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>  
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Scheduled Tickets</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <?php
                $sql = "SELECT t.TicketId, "
                        . "t.TicketNo, "
                        . "t.TicketStatus, "
                        . "t.TicketPaymentStatus, "
                        . "ps.PaymentStatus, "
                        . "a.Title, "
                        . "u.FirstName, "
                        . "c.MillName, "
                        . "s.start, "
                        . "ts.Ticket_status, "
                        . "d.Name "
                        . "FROM tickets t "
                        . "INNER JOIN ticket_status ts ON ts.Id = t.TicketStatus "
                        . "INNER JOIN payment_status ps ON ps.PayStatusId = t.TicketPaymentStatus "
                        . "INNER JOIN customers c ON t.CustomerId = c.CustomerId "
                        . "INNER JOIN users u ON u.UserId = c.UserId "
                        . "INNER JOIN districts d ON d.Id = u.DistrictId "
                        . "INNER JOIN titles a ON u.TitleId = a.Id "
                        . "INNER JOIN service_schedule s ON s.ticketId = t.TicketId $where ORDER BY s.start ASC";
                $result = $db->query($sql);
                ?>
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Customer Name</th>
                            <th>Mill / Company</th>
                            <th>District</th>
                            <th>Ticket Status</th>
                            <th>Payment</th>
                            <th>Scheduled Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $tid = $row['TicketId']; //to send through the button data to the modal
                                $olddate = $row['start']; //to send through the button data to the modal
                                $millname = $row['MillName'] //to send through the button data to the cancel modal
                                ?>
                                <tr>
                                    <td><?= $row['TicketNo'] ?></td>
                                    <td><?= $row['Title'] ?> <?= $row['FirstName'] ?></td>
                                    <td><?= $millname ?></td>
                                    <td><?= $row['Name'] ?></td>
                                    <td>
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
                                        ?>">
                                                  <?= $row['Ticket_status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="<?= $row['TicketPaymentStatus']=='1'?'badge badge-warning':'badge badge-success' ?>"><?= $row['PaymentStatus'] ?></span>
                                    </td>
                                    <td><?= $olddate ?></td>
                                    <td>
                                        <a href="<?= SYS_URL ?>tickets/ticket_details.php?ticketid=<?= $row['TicketId'] ?>" class="btn btn-primary <?= $privilege['Select'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-eye"></i> View</a>
                                        <!--disable  'Reschedule' & 'Cancel' buttons if ticket is already completed-->
                                        <button type="button" class="btn btn-warning" <?= $privilege['Edit'] == '0' ? 'disabled' : '' ?> data-bs-toggle="modal" data-bs-target="#reschedule" data-ticketid="<?= $tid ?>" data-olddate="<?= $olddate ?>" <?= $row['TicketStatus'] >= '5' ? 'disabled' : '' ?>><i class="fas fa-user-check"></i> Re-Schedule</button>
                                        <button type="button" class="btn btn-danger" <?= $privilege['Delete'] == '0' ? 'disabled' : '' ?> data-bs-toggle="modal" data-bs-target="#cancel" data-ticketid="<?= $tid ?>" data-millname="<?= $millname ?>" <?= $row['TicketStatus'] >= '4' ? 'disabled' : '' ?>> Cancel</button>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include '../layouts.php';
?>

<script>
    // use jQuery to pass data to the modal when button is clicked
    $(document).ready(function () {

        // event handler for reschedule modal
        $('#reschedule').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var ticket_id = button.data('ticketid'); // get data from the button and assign to a variable
            var old_date = button.data('olddate');

            var modal = $(this);
            modal.find('.modal-body input#reschedule_date').val(old_date);
            modal.find('.modal-footer input#ticket_id').val(ticket_id);
        });

        // event handler for cancel modal
        $('#cancel').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var ticket_id = button.data('ticketid');
            var mill_name = button.data('millname');

            var modal = $(this);
            modal.find('.modal-body span#mill').text(mill_name); // to insert data into a span use text(). not val()
            modal.find('.modal-footer input#ticket_id').val(ticket_id);
        });
    });
</script>