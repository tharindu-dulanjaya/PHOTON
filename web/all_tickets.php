<?php
include 'dashboard_header.php'; // header has the init.php 
$db = dbConn();

$UserId = $_SESSION['USERID'];

// get the customer id of the logged in user
$sql = "SELECT CustomerId FROM customers WHERE UserId='$UserId'";
$result = $db->query($sql);
$row = $result->fetch_assoc();
$customerId = $row['CustomerId']; // to use in sql query

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'cancel') {
    $tid = $_POST['ticket_id'];
    $sql = "UPDATE tickets SET TicketStatus='6' WHERE TicketId='$tid'";
    $db->query($sql);
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
                                <label class="form-label text-danger">Are you sure you want to cancel this Ticket?</label>
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
            <div class="row">
                <?php
                $sql = "SELECT * FROM tickets t "
                        . "LEFT JOIN service_schedule ss ON ss.ticketId = t.TicketId "
                        . "INNER JOIN common_machine_issues cmi ON cmi.Id=t.CommonIssueId "
                        . "INNER JOIN ticket_status ts ON ts.Id = t.TicketStatus "
                        . "WHERE t.CustomerId='$customerId' "
                        . "ORDER BY t.TicketStatus";
                $result = $db->query($sql);
                if ($result->num_rows > 0) { // means the customer has some schedules
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <div class="col-3 mb-3">
                            <div class="card <?php
                            if ($row['TicketStatus'] == '1') {
                                echo 'border-warning';
                            } elseif ($row['TicketStatus'] == '5') {
                                echo 'border-success';
                            } elseif ($row['TicketStatus'] == '6') {
                                echo 'border-danger';
                            } else {
                                echo 'border-info';
                            }
                            ?>">
                                <div class="card-header <?php
                                if ($row['TicketStatus'] == '1') {
                                    echo 'text-bg-warning';
                                } elseif ($row['TicketStatus'] == '5') {
                                    echo 'text-bg-success';
                                } elseif ($row['TicketStatus'] == '6') {
                                    echo 'text-bg-danger';
                                } else {
                                    echo 'text-bg-info';
                                }
                                ?>">
                                    <div class="row">
                                        <div class="col-9">
                                            <h5><?= $row['TicketNo'] ?></h5>
                                        </div>
                                        <div class="col-3 text-end">
                                            <a href="<?= WEB_URL ?>ticket_details.php?ticketid=<?= $row['TicketId'] ?>" class="btn btn-sm btn-dark">View</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
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
                                    ?>
                                          "> <?= $row['Ticket_status'] ?></span>
                                    <h4>
                                        <?php
                                        // if completed, show completed date, else show sheduled date
                                        if ($row['TicketStatus'] == '5') {
                                            echo $row['CompletedOn'];
                                        } elseif ($row['TicketStatus'] != '6') { // if cancelled(6), date is not displayed
                                            echo $row['start'];
                                        }
                                        ?>
                                    </h4><br>
                                    <h6>Issue: <b><?= $row['Issue'] ?></b></h6>
                                    <h6>Machine Model: <?= $row['ModelNo'] ?></h6>
                                    <h6>Serial No: <?= $row['SerialNo'] ?></h6>
                                    <h6>Created on: <?= $row['OpenedDate'] ?></h6>
                                </div>
                                <div class="card-footer">
                                    <?php
                                    // customer can cancel 'before working right now' status
                                    // cutomer cannot cancel Installation tickets. bcz they are created by the system
                                    if ($row['TicketStatus'] < '4' && $row['CommonIssueId'] != 1) {
                                        ?>
                                        <div class="text-end">
                                            <button type="button" class=" btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancel" data-ticketid="<?= $row['TicketId'] ?>" <?= $row['CommonIssueId'] == '1' ? 'disabled' : '' ?>> Cancel Ticket</button>
                                        </div>
                                        <?php
                                    }
                                    ?>

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
                            html: '<h5>You have not created any ticket yet!</h5>',
                            showCloseButton: false,
                            showConfirmButton: false,
                            timer: 4000
                        }).then(function() {
                            window.location.href = 'dashboard.php';
                        });
                      </script>";
                }
                ?>
            </div>

        </div>
    </section>
</main>
<?php
include 'dashboard_footer.php';
?>

<script>
// use jQuery to pass data to the modal when button is clicked
    $(document).ready(function () {

        // event handler to cancel modal
        $('#cancel').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var ticket_id = button.data('ticketid'); // get data from the button and assign to a variable

            var modal = $(this);
            modal.find('.modal-footer input#ticket_id').val(ticket_id); // assign data to the elements in modal
        });
    });
</script>

