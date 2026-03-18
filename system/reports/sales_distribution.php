<?php
ob_start();
include_once '../init.php';
$db = dbConn();

$link = "Geographical Sales Report";
$breadcrumb_item = "Reports";
$breadcrumb_item_active = "Chart";
?>   

<section class="content">
    <div class="container-fluid">
        <!--filter-->
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">            
            <div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="input-group mb-1">
                            <label class="input-group-text" for="from_date" >From</label>
                            <input type="date" class="form-control" id="from_date" name="from_date" value="<?= @$_POST['from_date'] ?>">
                        </div>
                    </div>
                    <div class="form-group col-md-3">
                        <div class="input-group">
                            <label class="input-group-text" for="to_date" >To</label>
                            <input type="date" class="form-control" id="to_date" name="to_date" value="<?= @$_POST['to_date'] ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-dark mb-2"><i class="fas fa-search"></i> Filter</button>
                    </div>
                </div>
            </div>
        </form>
        <!--Filter end-->
        <div class="row">
            <div class="col-md-5">
                <!-- table -->
                <div class="card card-dark">
                    <div class="card-header">
                        <h3 class="card-title">No. of Orders by District</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>                            
                        </div>
                    </div>
                    <div class="card-body table-responsive p-2">
                        <?php
                        $where = null;
                        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                            extract($_POST); // to get data from form fields
                            if (!empty($from_date) && !empty($to_date)) {
                                $where .= " order_date BETWEEN '$from_date' AND '$to_date' AND";
                            }
                            if (!empty($where)) {
                                $where = substr($where, 0, -3); // removes the last 3 characters (AND part)
                                $where = " WHERE $where"; // final complete WHERE statement
                            }
                        }
                        // order count by district
                        $sql = "SELECT order_date, delivery_district, COUNT(*) AS OrderCount "
                                . "FROM orders "
                                . "$where "
                                . "GROUP BY delivery_district";
                        $result = $db->query($sql);
                        if ($result->num_rows > 0) {
                            ?>
                            <table class="table table-hover text-nowrap" id="districtTable">
                                <thead>
                                    <tr>
                                        <th>District</th>
                                        <th>No. of Orders</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // for the chart
                                    $districts = [];
                                    $counts = [];
                                    while ($row = $result->fetch_assoc()) {
                                        // for the chart
                                        $districts[] = $row['delivery_district'];
                                        $counts[] = $row['OrderCount'];
                                        ?>
                                        <!--for table-->
                                        <tr>
                                            <td><?= $row['delivery_district'] ?></td>
                                            <td><?= $row['OrderCount'] ?></td>
                                        </tr>
                                        <?php
                                    }
                                    // for the chart
                                    // convert php variables in to javascript variables
                                    $districts_json = json_encode($districts);
                                    $counts_json = json_encode($counts);
                                } else {
                                    echo "<p class='text-primary mt-2'>No records found!</p>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- chart -->
            <div class="col-md-7">                
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Order Count by District</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="pieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>              
            </div>
        </div>

        <div class="row">
            <div class="col-md-5">
                <!-- table -->
                <div class="card card-dark">
                    <div class="card-header">
                        <h3 class="card-title">Order Value</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>                            
                        </div>
                    </div>
                    <div class="card-body table-responsive p-2">
                        <?php
                        $sql = "SELECT o.delivery_district, "
                                . "SUM(oi.unit_price * oi.qty) as amt "
                                . "FROM order_items oi "
                                . "INNER JOIN orders o ON o.id = oi.order_id "
                                . "$where "
                                . "GROUP BY o.delivery_district";
                        $result = $db->query($sql);
                        if ($result->num_rows > 0) {
                            ?>
                            <table class="table table-hover text-nowrap" id="summaryTable">
                                <thead>
                                    <tr>
                                        <th>District</th>
                                        <th>Order Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // for the chart
                                    $districts = [];
                                    $amounts = [];
                                    while ($row = $result->fetch_assoc()) {
                                        // for the chart
                                        $districts[] = $row['delivery_district'];
                                        $amounts[] = $row['amt'];
                                        ?>
                                        <!--for table-->
                                        <tr>
                                            <td><?= $row['delivery_district'] ?></td>
                                            <td><?= $row['amt'] ?></td>
                                        </tr>
                                        <?php
                                    }
                                    // for the chart
                                    // convert php variables in to javascript variables
                                    $districts_json = json_encode($districts);
                                    $amounts_json = json_encode($amounts);
                                } else {
                                    echo "<p class='text-primary mt-2'>No order details found!</p>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>              
            </div>
            <div class="col-md-7">
                <!-- chart -->
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">District vise Revenue</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="barChart2" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="pieChart2" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
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
        var barChartCanvas = $('#barChart').get(0).getContext('2d');
        var pieChartCanvas = $('#pieChart').get(0).getContext('2d');
        var barChartCanvas2 = $('#barChart2').get(0).getContext('2d');
        var pieChartCanvas2 = $('#pieChart2').get(0).getContext('2d');
        var districts = <?php echo $districts_json; ?>;
        var counts = <?php echo $counts_json; ?>;
        var amounts = <?php echo $amounts_json; ?>;

        // for the bar chart
        var barChartData = {
            labels: districts,
            datasets: [
                {
                    label: 'Orders Count',
                    backgroundColor: '#1dab90',
                    borderColor: '#1dab90',
                    pointRadius: false,
                    pointColor: '#3b8bba',
                    pointStrokeColor: 'rgba(60,141,188,1)',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(60,141,188,1)',
                    data: counts,
                    fill: false  // Ensure the area under the line is not filled
                }
            ]
        };
        
        var barChartData2 = {
            labels: districts,
            datasets: [
                {
                    label: 'Total Order Value',
                    backgroundColor: '#a56ce5', // column color
                    borderColor: '#1dab90',
                    pointRadius: false,
                    pointColor: '#3b8bba',
                    pointStrokeColor: 'rgba(60,141,188,1)',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(60,141,188,1)',
                    data: amounts,
                    fill: false  // Ensure the area under the line is not filled
                }
            ]
        };

        var barChartOptions = {
            maintainAspectRatio: false,
            responsive: true,
            legend: {
                display: true
            },
            scales: {
                xAxes: [{
                        gridLines: {
                            display: false
                        }
                    }],
                yAxes: [{
                        gridLines: {
                            display: true
                        }
                    }]
            }
        };

        // Create bar chart
        new Chart(barChartCanvas, {
            type: 'bar',
            data: barChartData,
            options: barChartOptions
        });
        // second bar chart
        new Chart(barChartCanvas2, {
            type: 'bar',
            data: barChartData2,
            options: barChartOptions
        });

        function getRandomColor() {
            var r = Math.floor(Math.random() * 255);
            var g = Math.floor(Math.random() * 255);
            var b = Math.floor(Math.random() * 255);
            return 'rgba(' + r + ',' + g + ',' + b + ',0.9)';
        }

        // Generate dynamic colors for each segment
        var pieColors = counts.map(() => getRandomColor());
        var pieColors2 = amounts.map(() => getRandomColor());

        var pieChartData = {
            labels: districts,
            datasets: [
                {
                    label: 'Order Count',
                    backgroundColor: pieColors, // Array of dynamically generated colors
                    borderColor: 'rgba(255,255,255,1)', // White border color
                    data: counts
                }
            ]
        };
        
        var pieChartData2 = {
            labels: districts,
            datasets: [
                {
                    label: 'Sales Value',
                    backgroundColor: pieColors2, // Array of dynamically generated colors
                    borderColor: 'rgba(255,255,255,1)', // White border color
                    data: amounts
                }
            ]
        };

        var pieChartOptions = {
            maintainAspectRatio: false,
            responsive: true,
            legend: {
                display: true
            }
        };

        // Create pie chart
        new Chart(pieChartCanvas, {
            type: 'pie',
            data: pieChartData,
            options: pieChartOptions
        });
        
        // Create second pie chart
        new Chart(pieChartCanvas2, {
            type: 'pie',
            data: pieChartData2,
            options: pieChartOptions
        });

        // data table
        $(function () {
            $("#summaryTable").DataTable({
                "responsive": true,
                "paging": true,
                "searching": false,
                "ordering": false,
                "info": true,
                "lengthChange": false,
                "autoWidth": false,
                "buttons": ["pdf"]
            }).buttons().container().appendTo('#summaryTable_wrapper .col-md-6:eq(0)');

            $("#districtTable").DataTable({
                "responsive": true,
                "paging": true,
                "searching": false,
                "ordering": false,
                "info": true,
                "lengthChange": false,
                "autoWidth": false,
                "buttons": ["pdf"]
            }).buttons().container().appendTo('#districtTable_wrapper .col-md-6:eq(0)');
        });
    });


</script>