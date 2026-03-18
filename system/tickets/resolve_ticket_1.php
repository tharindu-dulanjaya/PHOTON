<?php
ob_start();
include_once '../init.php';

$db = dbConn();
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    extract($_GET); //to get the ticket id
    $sql = "SELECT "
            . "tickets.TicketId,"
            . "tickets.TicketNo,"
            . "tickets.OpenedDate, "
            . "tickets.ModelNo, "
            . "tickets.SerialNo, "
            . "tickets.Description, "
            . "tickets.MachineCatId, "
            . "tickets.TicketStatus, "
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
            . "common_machine_issues.ReqTechnicians, "
            . "orders.order_date "
            . "FROM tickets "
            . "INNER JOIN customers ON tickets.CustomerId = customers.CustomerId "
            . "INNER JOIN users ON users.UserId = customers.UserId "
            . "LEFT JOIN orders ON orders.id=tickets.OrderId "
            . "INNER JOIN titles ON users.TitleId = titles.Id "
            . "INNER JOIN districts ON districts.Id = users.DistrictId "
            . "INNER JOIN item_category ON tickets.MachineCatId = item_category.id "
            . "INNER JOIN common_machine_issues ON tickets.CommonIssueId = common_machine_issues.Id "
            . "WHERE tickets.TicketId='$ticketid'";

    $result = $db->query($sql);
    $row = $result->fetch_assoc();

    $ticket_id = $row['TicketId'];
    $ticket_no = $row['TicketNo'];
    $opened_date = $row['OpenedDate'];
    $status = $row['TicketStatus'];

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
    $purchase_date = $row['order_date'];
    $issue = $row['Issue'];
    $required_techs = $row['ReqTechnicians'];
    $comments = $row['Description'];
}

// if ticket is already resolved, user should not access this interface again
if($status != 1){
    echo "<script>
            Swal.fire({
                icon: 'warning',
                title: '',
                html: '<h4>Ticket is resolved already</h4>',
                showCloseButton: false,
                showConfirmButton: false,
                timer: 3500
            }).then(function() {
                window.location.href = 'manage.php';
            });
          </script>";
}

$link = "Resolve Ticket # " . $ticket_no;
$breadcrumb_item = "Tickets";
$breadcrumb_item_active = "Resolve";
?> 
<a href="<?= SYS_URL ?>tickets/manage.php" class="btn btn-outline-dark mb-2"><i class="fas fa-arrow-left "> </i> Back</a>
<div class="row">

    <div class="col-md-5">
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
                    <label for="purchase_date" class="col-sm-5 col-form-label">Purchased Date</label>
                    <div class="col-sm-7">
                        <input type="text" name="purchase_date" id="purchase_date" class="form-control-plaintext" value="<?= @$purchase_date ?>" readonly>
                    </div>
                </div>
                <div class="row">
                    <label for="issue" class="col-sm-5 col-form-label">Issue with the machine</label>
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
    </div>
    <div class="col-4">
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
    <div class="col-3">
        <div class="card">
            <div class="card-header bg bg-dark">
                <h3 class="card-title">Ticket Information</h3>
            </div>
            <div class="card-body">
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
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Technicians section -->
            <div class="col-md-3">
                <div class="sticky-top mb-3">
                    <div class="card">
                        <div class="card-header">
                            <!--suggested technicians based on their skilled category, skill level and required persons according to the issue -->
                            <h4 class="card-title">Suggested Technicians</h4>
                        </div>
                        <div class="card-body">
                            <div id="external-events">
                                <?php
                                $sql = "SELECT e.EmployeeId, u.FirstName, u.LastName "
                                        . "FROM technicians t "
                                        . "INNER JOIN employees e ON e.EmployeeId = t.EmpId "
                                        . "INNER JOIN users u ON u.UserId = e.UserId "
                                        . "WHERE t.SkilledCategory = '$machine_cat_id' ORDER BY t.SkillLevel DESC LIMIT $required_techs";

                                $result = $db->query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <div class="external-event bg-warning" value=""><?= $row['EmployeeId'] ?> - <?= $row['FirstName'] ?> <?= $row['LastName'] ?></div>
                                    <?php
                                }
                                ?>
                                <!--hidden checkbox to remove the element after dragging-->
                                <div class="checkbox">
                                    <label for="drop-remove">
                                        <input type="checkbox" id="drop-remove" checked hidden>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Select Technicians Manually</h3>
                        </div>
                        <div class="card-body">
                            <div class="btn-group" style="width: 100%; margin-bottom: 10px;">
<!--                                <ul class="fc-color-picker" id="color-chooser">
                                    <li><a class="text-primary" href="#"><i class="fas fa-square"></i></a></li>
                                    <li><a class="text-warning" href="#"><i class="fas fa-square"></i></a></li>
                                    <li><a class="text-success" href="#"><i class="fas fa-square"></i></a></li>
                                    <li><a class="text-danger" href="#"><i class="fas fa-square"></i></a></li>
                                    <li><a class="text-muted" href="#"><i class="fas fa-square"></i></a></li>
                                </ul>-->
                            </div>
                            <div class="input-group">
                                <?php
                                $sql = "SELECT e.EmployeeId, u.FirstName, u.LastName FROM technicians t "
                                        . "INNER JOIN employees e ON e.EmployeeId = t.EmpId "
                                        . "INNER JOIN users u ON u.UserId = e.UserId "
                                        . "WHERE t.SkilledCategory != '$machine_cat_id'";
                                $result = $db->query($sql);
                                ?>
                                <select class="form-control" id="new-event" name="department_id">
                                    <option value="">Select Technician</option>
                                    <?php while ($row = $result->fetch_assoc()) { ?>
                                        <option><?= $row['EmployeeId'] ?> - <?= $row['FirstName'] ?> <?= $row['LastName'] ?></option>
                                    <?php } ?>
                                </select>
                                <div class="input-group-append">
                                    <button id="add-new-event" type="button" class="btn btn-primary">Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5">
                        <button id="save-events" type="button" class="btn btn-success" style="width: 100%">Save & Update</button>
                    </div>
                </div>
            </div>
            <!-- Calender section -->
            <div class="col-md-9">
                <div class="card card-primary">
                    <div class="card-body p-0">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
include '../layouts.php';
?>

<script>

    $(document).ready(function () {

        // array to store events before sending to the database
        var eventsToAdd = [];

        //initialize the external events
        function ini_events(ele) {
            ele.each(function () {

                // Create an Event Object
                var eventObject = {
                    title: $.trim($(this).text()) // use the element's text as the event title
                }

                // store the Event Object in the DOM element so we can get to it later
                $(this).data('eventObject', eventObject)

                // make the event draggable using jQuery UI
                $(this).draggable({
                    zIndex: 1070,
                    revert: true, // will cause the event to go back to its
                    revertDuration: 0  //  original position after the drag
                })

            })
        }

        ini_events($('#external-events div.external-event'))

        var Calendar = FullCalendar.Calendar;
        var Draggable = FullCalendar.Draggable;
        var containerEl = document.getElementById('external-events');
        //var checkbox = document.getElementById('drop-remove');
        var calendarEl = document.getElementById('calendar');

        new Draggable(containerEl, {
            itemSelector: '.external-event',
            eventData: function (eventEl) {
                return {

                    title: eventEl.innerText,
                    backgroundColor: window.getComputedStyle(eventEl, null).getPropertyValue('background-color'),
                    borderColor: window.getComputedStyle(eventEl, null).getPropertyValue('background-color'),
                    textColor: window.getComputedStyle(eventEl, null).getPropertyValue('color'),
                };
            }
        });
        var calendar = new Calendar(calendarEl, {
            headerToolbar: {
                left: '',
                center: 'title',
                right: 'prev,next'
            },
            themeSystem: 'bootstrap',
            events: 'getServiceSchedule.php',
            eventClick: function (info) {
                info.jsEvent.preventDefault();

                Swal.fire({
                    icon: "info",
                    title: info.event.extendedProps.millName, //extended properties
                    html: '<p> Issue : ' + info.event.extendedProps.description + '</p>',
                    showCloseButton: false,
                    showConfirmButton: false,
                    confirmButtonText: '',
                    timer: 2000
                });
            },
            editable: true,
            droppable: true, // this allows things to be dropped onto the calendar
            drop: function (info) {
                //technician name remove from list after dragging
                info.draggedEl.parentNode.removeChild(info.draggedEl);

                //collect event data before sending to database

                var eventData = {
                    title: info.draggedEl.innerText,
                    ticketId: document.getElementById('ticket_id').value,
                    millName: document.getElementById('mill_name').value,
                    issue: document.getElementById('issue').value,
                    start: info.dateStr,
                    backgroundColor: window.getComputedStyle(info.draggedEl, null).getPropertyValue('background-color'),
                    borderColor: window.getComputedStyle(info.draggedEl, null).getPropertyValue('background-color')
                };
                // add event to local array
                eventsToAdd.push(eventData);
            }
        });
        calendar.render();
        // $('#calendar').fullCalendar()

        /* ADDING EVENTS */
        var currColor = '#3c8dbc' //Red by default
        // Color chooser button
        $('#color-chooser > li > a').click(function (e) {
            e.preventDefault()
            // Save color
            currColor = $(this).css('color')
            // Add color effect to button
            $('#add-new-event').css({
                'background-color': currColor,
                'border-color': currColor
            })
        });
        $('#add-new-event').click(function (e) {
            e.preventDefault()
            // Get value and make sure it is not null
            var val = $('#new-event').val()
            if (val.length == 0) {
                return
            }

            // Create events
            var event = $('<div />')
            event.css({
                'background-color': currColor,
                'border-color': currColor,
                'color': '#fff'
            }).addClass('external-event')
            event.text(val)
            $('#external-events').prepend(event)

            // Add draggable funtionality
            ini_events(event)

            // Remove event from text input
            $('#new-event').val('')
        });

        // save and update button click -> save events to the database
        $('#save-events').click(function (e) {
            e.preventDefault();

            if (eventsToAdd.length === 0) {
                Swal.fire({
                    icon: "warning",
                    title: 'Please assign Technicians to save',
                    html: '',
                    showCloseButton: true,
                    showConfirmButton: false,
                    confirmButtonText: 'Close'
                });
                return;
            }

            // send AJAX request to insert events into database
            $.ajax({
                url: 'insertServiceEvents.php',
                method: 'POST',
                data: {events: JSON.stringify(eventsToAdd)},
                success: function (response) {
                    var ticketId = JSON.parse(response).ticketId; // response contains the ticketId from the server side
                    Swal.fire({
                        icon: "success",
                        title: '',
                        html: '<p>Technicians Assigned Successfully! </p>',
                        showCloseButton: false,
                        showConfirmButton: false,
                        timer: 2500
                    }).then(function () {
                        window.location.href = 'ticket_details.php?ticketid=' + ticketId; // redirects to details page
                    });
                    console.log(response);
                    eventsToAdd = [];
                    //calendar.refetchEvents();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error(textStatus, errorThrown);
                }
            });
        });
    });
</script>