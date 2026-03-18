<?php
ob_start();
include_once '../init.php';
$db = dbConn();

$link = "Technician Schedule";
$breadcrumb_item = "Technician";
$breadcrumb_item_active = "Schedule";

extract($_GET); // get $id from url (employee Id)
?> 
<div class="row">
    <div class="col-12">
        <!--top buttons area-->
        <div class="mb-2">
            <a href="manage.php" class="btn btn-outline-dark mb-2"><i class="fas fa-arrow-left">  </i> View All</a>
        </div>
        <div class="row">
            <?php
            // cancelled tickets(status->6) should not be shown

            $sql = "SELECT * FROM ticket_technicians tt "
                    . "INNER JOIN tickets t ON t.TicketId = tt.TicketId "
                    . "INNER JOIN service_schedule ss ON ss.ticketId = t.TicketId "
                    . "INNER JOIN ticket_status ts ON ts.Id = t.TicketStatus "
                    . "WHERE tt.EmployeeId = '$id' AND t.TicketStatus <> 6 "
                    . "ORDER BY ss.start";
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
                                <!-- 'mark as' buttons are only displayed for technicians, in their interface-->
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
                            html: '<h4>This technician is currently not assigned to any service!</h4>',
                            showCloseButton: false,
                            showConfirmButton: false,
                            timer: 3000
                        }).then(function() {
                            window.location.href = 'manage.php';
                        });
                      </script>";
            }
            ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include '../layouts.php';
?>
