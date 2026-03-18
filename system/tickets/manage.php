<?php
ob_start();
session_start(); // to check permission
include_once '../init.php';

// check permission of the user
$current_url = $_SERVER['REQUEST_URI'];
if (!checkPermission($current_url, $_SESSION['USERID'])) {
    header("Location:../unauthorized.php");
}
$db = dbConn();
$link = "All Tickets";
$breadcrumb_item = "Tickets";
$breadcrumb_item_active = "Manage";

// check the CRUD privileges for the logged in user
$privilege = checkprivilege('7');
?> 
<div class="row">
    <div class="col-12">
        <!--Filter section-->
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">            
            <p class="d-inline-flex gap-1">
                <button class="btn btn-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </p>
            <a href="<?= SYS_URL ?>tickets/scheduled_services.php" class="btn btn-outline-primary"> Resolved Tickets</a>
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
                                    $sql = "SELECT * FROM common_machine_issues";
                                    $result = $db->query($sql);
                                    ?>
                                    <select class="form-control" name="common_issue">
                                        <option value="" readonly>Filter By Common Issue</option>
                                        <?php while ($row = $result->fetch_assoc()) { ?>
                                            <option value="<?= $row['Id'] ?>" <?= @$_POST['common_issue'] == $row['Id'] ? 'selected' : '' ?>><?= $row['Issue'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-dark float-right"><i class="fas fa-search"></i> Filter</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!--Filter end-->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Opened Tickets</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <?php
                //this where part executes with the filter button clicked(POST)
                $where = null;
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    extract($_POST); // to get data from form fields
                    // if someone filter from date, it will be submitted by the form using POST method. then the sql query will be updated with WHERE clause
                    if (!empty($from_date) && !empty($to_date)) {
                        $where .= " t.OpenedDate BETWEEN '$from_date' AND '$to_date' AND";
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
                    if (!empty($common_issue)) {
                        $where .= " t.CommonIssueId = '$common_issue' AND";
                    }
                    if (!empty($where)) {
                        $where = substr($where, 0, -3); // removes the last 3 characters (AND part)
                        $where = " WHERE $where"; // final complete WHERE statement
                    }
                } else {
                    // must required where part. this executes when the page loads without post(not filtered)
                    $where = null;
                }
                $sql = "SELECT t.TicketId, t.TicketNo, t.TicketStatus, a.Title, u.FirstName, c.MillName, "
                        . "t.OpenedDate, t.CommonIssueId, i.category_name, q.Issue, t.Description, ts.Ticket_status "
                        . "FROM tickets t "
                        . "INNER JOIN customers c ON t.CustomerId = c.CustomerId "
                        . "INNER JOIN users u ON u.UserId = c.UserId "
                        . "INNER JOIN titles a ON u.TitleId = a.Id "
                        . "INNER JOIN item_category i ON t.MachineCatId = i.id "
                        . "INNER JOIN ticket_status ts ON ts.Id = t.TicketStatus "
                        . "INNER JOIN common_machine_issues q ON t.CommonIssueId = q.Id "
                        . "$where "
                        . "ORDER BY t.TicketStatus ASC, t.OpenedDate DESC";
                $result = $db->query($sql);
                ?>
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Customer</th>
                            <th>Mill / Company</th>
                            <th>Opened Date</th>
                            <th>Machine Category</th>
                            <th>Issue</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?= $row['TicketNo'] ?></td>
                                    <td><?= $row['Title'] ?> <?= $row['FirstName'] ?></td>
                                    <td><?= $row['MillName'] ?></td>
                                    <td><?= $row['OpenedDate'] ?></td>
                                    <td><?= $row['category_name'] ?></td>
                                    <td><?= $row['CommonIssueId'] == '0' ? '<b>Installation</b>' : $row['Issue'] ?></td>
                                    <td>
                                        <span class="
                                        <?php
                                        if ($row['TicketStatus'] == '1') {
                                            echo 'badge badge-warning';
                                        } elseif ($row['TicketStatus'] == '2') {
                                            echo 'badge badge-info';
                                        } elseif ($row['TicketStatus'] == '3') {
                                            echo 'badge badge-info';
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
                                        <?php
                                        if ($row['TicketStatus'] == '1') { // display resolve button only if not resolved
                                            ?>
                                            <a href="<?= SYS_URL ?>tickets/resolve_ticket.php?ticketid=<?= $row['TicketId'] ?>" class="btn btn-primary <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-user-check"></i> Resolve</a>
                                            <?php
                                        } else {
                                            ?>
                                            <a href="<?= SYS_URL ?>tickets/ticket_details.php?ticketid=<?= $row['TicketId'] ?>" class="btn btn-dark" style="width: 90%"><i class="fas fa-eye"></i> View</a>
                                            <?php
                                        }
                                        ?>
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