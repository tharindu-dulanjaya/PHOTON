<?php
ob_start();
include 'dashboard_header.php';
// only customers should be allowed to view the dashboard. access is checked in the dashboard_header.php file
?>
<main id="main">
    <!--Breadcrumb section-->
    <section class="breadcrumbs">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Dashboard</h2>
                <ol>
                    <li><b>Customer</b></li>
                    <li>Dashboard</li>
                </ol>
            </div>
        </div>
    </section>

    <!--Dashboard Content Area-->
    <section class="inner-page">                
        <div class="container">

            <?php
            // alert box to checkout, if there are products in the cart
            if (!empty($_SESSION['cart'])) {
                ?>
                <div class="alert alert-success alert-dismissible fade show  col-5" role="alert">
                    You have left some products in your cart.. &nbsp;&nbsp;&nbsp;&nbsp;
                    <a href="<?= WEB_URL ?>cart.php" class="btn btn-sm btn-success float-right"><b>View Cart</b></a>                       
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php
            }
            if (isset($_SESSION['ticket']) && $_SESSION['ticket'] == 'new') {
                ?>
                <div class="alert alert-warning alert-dismissible fade show  col-5" role="alert">
                    You were trying to request a service.. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <a href="<?= WEB_URL ?>create_ticket.php" class="btn btn-sm btn-warning float-right"><b>Create Ticket Now</b></a>                       
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php
                unset($_SESSION['ticket']);
            }
            ?>
            
            <div class="row">                
                <div class="col-md-3">
                    <div class="card" >
                        <a href="<?= WEB_URL ?>shop.php"><img src="assets/img/hero-machine.png" class="card-img-top" alt="..."></a>
                        <div class="card-body">
                            <h4 class="card-title text-center">View Products</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card" >
                        <a href="<?= WEB_URL ?>create_ticket.php"><img src="assets/img/tech-support.png" class="card-img-top" alt="..."></a>
                        <div class="card-body">
                            <h4 class="card-title text-center">Request Repair</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card" >
                        <a href="<?= WEB_URL ?>my_orders.php"><img src="assets/img/purchased-machines.png" class="card-img-top" alt="..."></a>
                        <div class="card-body">
                            <h4 class="card-title text-center">My Machines</h4>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card" >
                        <a href="<?= WEB_URL ?>all_tickets.php"><img src="assets/img/service-schedule.png" class="card-img-top" alt="..."></a>
                        <div class="card-body">
                            <h4 class="card-title text-center">My Tickets</h4>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="row mt-4">
                
                <div class="col-md-3">
                    <div class="card" >
                        <a href="<?= WEB_URL ?>payments.php"><img src="assets/img/payments.png" class="card-img-top" alt="..."></a>
                        <div class="card-body">
                            <h4 class="card-title text-center">Payments</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card" >
                        <a href="<?= WEB_URL ?>profile.php"><img src="assets/img/edit-profile.jpg" class="card-img-top" alt="..."></a>
                        <div class="card-body">
                            <h4 class="card-title text-center">Edit Profile</h4>
                        </div>
                    </div>
                </div>
<!--                <div class="col-md-3">
                    <div class="card" >
                        <a href="<?= WEB_URL ?>chat"><img src="assets/img/chat forum.jpg" class="card-img-top" alt="..."></a>
                        <div class="card-body">
                            <h4 class="card-title text-center">Customer Chat Forum</h4>
                        </div>
                    </div>
                </div>-->
            </div>

            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                extract($_POST);

                $userid = $_SESSION['USERID'];
                $db = dbConn();

                //need to calculate the endtime of a appointment
                $time_duration = '01:00:00';

                //convert string time to a time value by php
                $starttime = strtotime($time);
                $endtime = date("H:i:s", strtotime("+60 minutes", $starttime));
                // H: Hours, i: minutes, s: seconds  (+ addtime) (- substract time)
                //need to find the customer_id from the user_id
                $sql = "SELECT * FROM customers WHERE UserId = '$userid'";
                $result = $db->query($sql);
                $row = $result->fetch_assoc();
                $customer_id = $row['CustomerId'];

                $sql = "INSERT INTO appointments(Customer_id, Date, Start_time, End_time) VALUES ('$customer_id','$date','$time','$endtime')";
                $db->query($sql);

                //after inserting data in to database, we don't need these session data anymore. so we unset them. (not destroy)
                unset($_SESSION['action']);
                unset($_SESSION['date']);
                unset($_SESSION['time']);

                echo "<div class='alert alert-success'><b>Your Booking is Successful!</b></div>";

                //ISSUE : if customer refreshes the page the booking is inserted again to the database
            }

            if (isset($_SESSION['action'])) {
                if ($_SESSION['action'] == 'booking') {
                    echo $_SESSION['date'];
                    echo $_SESSION['time'];
                    ?> 
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                        <input type="hidden" name="date" value="<?= $_SESSION['date'] ?>">
                        <input type="hidden" name="time" value="<?= $_SESSION['time'] ?>">
                        <button type="submit" class="btn btn-warning">Click to confirm your booking </button>
                    </form>

                    <?php
                }
            }
            ?>
        </div>
    </section>
</main>

<?php
include 'dashboard_footer.php';
ob_end_flush();
?>