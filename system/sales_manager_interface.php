<?php
ob_start();
$db = dbConn();

$link = "Dashboard";
$breadcrumb_item = "Dashboard";
$breadcrumb_item_active = "View";
?> 
<div class="row">
    <div class="col-12">
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <!--Filter section-->
            <p class="d-inline-flex gap-1">
                <button class="btn btn-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                    <i class="fas fa-filter"></i> Filter
                </button>                
            </p>
            <a href="<?= SYS_URL ?>orders/coupon_discounts.php" class="btn btn-outline-success <?= $privilege['Add'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-percentage"></i> Coupon Discounts</a>
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
                                    <input type="text" class="form-control" id="order_no" name="order_no" placeholder="Filter By Order #" value="<?= @$_POST['order_no'] ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <input type="text" class="form-control" id="customer" name="customer" placeholder="Customer Name" value="<?= @$_POST['customer'] ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <input type="text" class="form-control" id="mill_name" name="mill_name" placeholder="Mill/Company" value="<?= @$_POST['mill_name'] ?>">
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
                                <div class="form-group col-md-4">
                                    <?php
                                    $sql = "SELECT * FROM payment_methods WHERE PayMethodStatus='1'";
                                    $result = $db->query($sql);
                                    ?>
                                    <select class="form-control" name="payment_method">
                                        <option value="" readonly>Filter By Payment Method</option>
                                        <?php while ($row = $result->fetch_assoc()) { ?>
                                            <option value="<?= $row['PayMethodId'] ?>" <?= @$_POST['payment_method'] == $row['PayMethodId'] ? 'selected' : '' ?>><?= $row['PaymentMethod'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <?php
                                    $sql = "SELECT * FROM order_status";
                                    $result = $db->query($sql);
                                    ?>
                                    <select class="form-control" name="order_status">
                                        <option value="" readonly>Filter By Order Status</option>
                                        <?php while ($row = $result->fetch_assoc()) { ?>
                                            <option value="<?= $row['StatusId'] ?>" <?= @$_POST['order_status'] == $row['StatusId'] ? 'selected' : '' ?>><?= $row['OrderStatus'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-1">
                                </div>
                                <div class="form-group col-md-3">
                                    <button type="submit" class="btn btn-dark float-right"><i class="fas fa-search"></i> Filter</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Order Details</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <?php
                // if someone filter from date, it will be submitted by the form using POST method. then the sql query will be updated with WHERE clause
                $where = null;
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    extract($_POST); // to get data from form fields
                    if (!empty($from_date) && !empty($to_date)) {
                        $where .= " o.order_date BETWEEN '$from_date' AND '$to_date' AND";
                    }
                    if (!empty($order_no)) {
                        $where .= " o.order_number LIKE '%$order_no%' AND";
                    }
                    if (!empty($customer)) {
                        $where .= " u.FirstName LIKE '%$customer%' AND";
                    }
                    if (!empty($mill_name)) {
                        $where .= " c.MillName LIKE '%$mill_name%' AND";
                    }
                    if (!empty($district)) {
                        $where .= " u.DistrictId = '$district' AND";
                    }
                    if (!empty($payment_method)) {
                        $where .= " o.payment_method = '$payment_method' AND";
                    }
                    if (!empty($order_status)) {
                        $where .= " o.order_status = '$order_status' AND";
                    }
                    if (!empty($where)) {
                        $where = substr($where, 0, -3); // removes the last 3 characters (AND part)
                        $where = " WHERE $where"; // final complete WHERE statement
                    }
                }

                $sql = "SELECT o.id, "
                        . "o.order_date, "
                        . "u.FirstName, "
                        . "u.LastName, "
                        . "u.DistrictId, "
                        . "c.MillName, "
                        . "o.delivery_district, "
                        . "o.order_number, "
                        . "o.order_status, "
                        . "s.OrderStatus, "
                        . "p.PaymentMethod, "
                        . "d.Name "
                        . "FROM orders o "
                        . "INNER JOIN customers c ON o.customer_id = c.CustomerId "
                        . "INNER JOIN users u ON c.UserId = u.UserId "
                        . "INNER JOIN districts d ON u.DistrictId = d.Id "
                        . "LEFT JOIN order_status s ON s.StatusId = o.order_status "
                        . "LEFT JOIN payment_methods p ON p.PayMethodId = o.payment_method "
                        . "$where "
                        . "ORDER BY o.order_date DESC";
                $result = $db->query($sql);
                ?>

                <table class="table table-hover text-nowrap" id="myTable">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Order Date</th>
                            <th>Customer Name</th>
                            <th>Mill / Company</th>
                            <th>District</th>
                            <th>Payment Method</th>
                            <th>Order Status</th>
                            <th>Invoice</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?= $row['order_number'] ?></td>
                                    <td><?= $row['order_date'] ?></td>
                                    <td><?= $row['FirstName'] ?> <?= $row['LastName'] ?></td>
                                    <td><?= $row['MillName'] ?></td>
                                    <td><?= $row['Name'] ?></td>
                                    <td><?= $row['PaymentMethod'] ?></td>
                                    <td>
                                        <?php
                                        if ($row['order_status'] == 1) {
                                            echo "<span class='badge badge-warning' style='width:100%'>" . $row['OrderStatus'] . "</span>";
                                        } elseif ($row['order_status'] == 2) {
                                            echo "<span class='badge badge-danger' style='width:100%'>" . $row['OrderStatus'] . "</span>";
                                        } elseif ($row['order_status'] == 3) {
                                            echo "<span class='badge badge-info' style='width:100%'>" . $row['OrderStatus'] . "</span>";
                                        } elseif ($row['order_status'] == 4) {
                                            echo "<span class='badge badge-primary' style='width:100%'>" . $row['OrderStatus'] . "</span>";
                                        } elseif ($row['order_status'] == 5) {
                                            echo "<span class='badge badge-dark' style='width:100%'>" . $row['OrderStatus'] . "</span>";
                                        } elseif ($row['order_status'] == 6) {
                                            echo "<span class='badge badge-success' style='width:100%'>" . $row['OrderStatus'] . "</span>";
                                        } elseif ($row['order_status'] == 7) { // cancelled
                                            echo "<span class='badge badge-danger' style='width:100%'>" . $row['OrderStatus'] . "</span>";
                                        } elseif ($row['order_status'] == 8) { // returned
                                            echo "<span class='badge badge-danger' style='width:100%'>" . $row['OrderStatus'] . "</span>";
                                        } else { // completed
                                            echo "<span class='badge badge-success' style='width:100%'>" . $row['OrderStatus'] . "</span>";
                                        }
                                        ?>
                                    </td>
                                    <td><a href="payments/invoice.php?order_id=<?= $row['id'] ?>" class="text-info <?= $privilege['Select'] == '0' ? 'disabled' : '' ?>"><i class="fas fa-paper-plane"></i></a></td>
                                    <td><a href="orders/view_order_items.php?order_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-dark <?= $privilege['Select'] == '0' ? 'disabled' : '' ?>"> View &nbsp; <i class="fas fa-arrow-right"></i></a></td>
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
include 'layouts.php';
?>

<script>
    $(document).ready(function () {
        checkForNewOrders();

        // calls the function in every 3 seconds
        setInterval(checkForNewOrders, 3000);

        function checkForNewOrders() {

            $.ajax({
                url: 'checkForNewOrders.php',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.new_order_flag) {
                        playSound('../assets/mixkit-access-allowed-tone-2869.wav');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }

        function playSound(url) {
            var audio = new Audio(url);
            audio.play();
        }
    });
</script>