<?php
ob_start();
$db = dbConn();

$link = "Dashboard";
$breadcrumb_item = "Dashboard";
$breadcrumb_item_active = "View";
?>

<!--<div class="row">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-cog"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Tickets</span>
                <span id="NumberOfTickets" class="info-box-number"></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-user-tie"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Employees</span>
                <span id="NumberOfEmployees" class="info-box-number"></span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-shopping-cart"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Sales</span>
                <span id="NumberOfOrders" class="info-box-number"></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Customers</span>
                <span id="NumberOfCustomers" class="info-box-number"></span>
            </div>
        </div>
    </div>
</div>-->
<?php
$content = ob_get_clean();
include 'layouts.php';
?>

<script>
    // $ means -> jQuery
    // when the page is loaded/ready
    $(document).ready(function () {
        getNumberOfOrders();
        getNumberOfTickets();
        getNumberOfEmployees();
        getNumberOfCustomers();

        // calls the function in every 3 seconds
        setInterval(getNumberOfOrders, 3000);
        setInterval(getNumberOfTickets, 3000);
        setInterval(getNumberOfEmployees, 3000);
        setInterval(getNumberOfCustomers, 3000);
        
        function getNumberOfOrders() {
            $.ajax({
                url: 'orders/getNumberOfOrders.php',
                type: 'GET',
                success: function (data) {
                    $("#NumberOfOrders").html(data);
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }
        function getNumberOfTickets() {

            $.ajax({
                url: 'tickets/getNumberOfTickets.php',
                type: 'GET',
                success: function (data) {
                    $("#NumberOfTickets").html(data);
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }
        function getNumberOfEmployees() {

            $.ajax({
                url: 'employees/getNumberOfEmployees.php',
                type: 'GET',
                success: function (data) {
                    $("#NumberOfEmployees").html(data);
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }
        function getNumberOfCustomers() {

            $.ajax({
                url: 'customers/getNumberOfCustomers.php',
                type: 'GET',
                success: function (data) {
                    $("#NumberOfCustomers").html(data);
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }
    });
</script>