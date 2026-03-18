<?php
ob_start();
include_once '../init.php';
$db = dbConn();

$link = "Customer Summary";
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
                        <h3 class="card-title">Registrations</h3>
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
                                $where .= " c.RegisteredDate BETWEEN '$from_date' AND '$to_date' AND";
                            }
                            if (!empty($where)) {
                                $where = substr($where, 0, -3); // removes the last 3 characters (AND part)
                                $where = " WHERE $where"; // final complete WHERE statement
                            }
                        }

                        $sql = "SELECT 
                                    YEAR(c.RegisteredDate) AS Year, 
                                    MONTH(c.RegisteredDate) AS MonthNo, 
                                    DATE_FORMAT(c.RegisteredDate, '%M') AS Month,
                                    COUNT(c.CustomerId) AS NewCustomers
                                FROM 
                                    customers c 
                                $where 
                                GROUP BY 
                                    YEAR(c.RegisteredDate), MONTH(c.RegisteredDate)
                                ORDER BY 
                                    Year, MonthNo";
                        $result = $db->query($sql);
                        if ($result->num_rows > 0) {
                            ?>
                            <table class="table table-hover text-nowrap" id="summaryTable">
                                <thead>
                                    <tr>
                                        <th>Year & Month</th>
                                        <th>New Customers</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // for the chart
                                    $months = [];
                                    $amounts = [];
                                    while ($row = $result->fetch_assoc()) {
                                        // for the chart
                                        $months[] = $row['Year'] . ' ' . $row['Month'];
                                        $amounts[] = $row['NewCustomers'];
                                        ?>
                                        <!--for table-->
                                        <tr>
                                            <td><?= $row['Year'] . ' ' . $row['Month'] ?></td>
                                            <td><?= $row['NewCustomers'] ?></td>
                                        </tr>
                                        <?php
                                    }
                                    // for the chart
                                    // convert php variables in to javascript variables
                                    $months_json = json_encode($months);
                                    $amounts_json = json_encode($amounts);
                                } else {
                                    echo "<p class='text-primary mt-2'>No any issues recorded yet</p>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>              
            </div>
            <div class="col-md-7">
                <!-- chart -->
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Customer Registrations</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <!--canvas is a html 5 element-->
                            <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <!--canvas is a html 5 element-->
                            <canvas id="lineChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
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
                        <h3 class="card-title">Customers by District</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>                            
                        </div>
                    </div>
                    <div class="card-body table-responsive p-2">
                        <?php
                        $sql = "SELECT
                                    d.Name,
                                    u.DistrictId,
                                    c.RegisteredDate,
                                    COUNT(u.UserId) AS CustomerCount
                                FROM users u
                                INNER JOIN customers c ON c.UserId=u.UserId 
                                INNER JOIN districts d ON d.Id = u.DistrictId
                                $where 
                                GROUP BY u.DistrictId";
                        $result = $db->query($sql);
                        if ($result->num_rows > 0) {
                            ?>
                            <table class="table table-hover text-nowrap" id="districtTable">
                                <thead>
                                    <tr>
                                        <th>District</th>
                                        <th>No. of Customers</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // for the chart
                                    $districts = [];
                                    $counts = [];
                                    while ($row = $result->fetch_assoc()) {
                                        // for the chart
                                        $districts[] = $row['Name'];
                                        $counts[] = $row['CustomerCount'];
                                        ?>
                                        <!--for table-->
                                        <tr>
                                            <td><?= $row['Name'] ?></td>
                                            <td><?= $row['CustomerCount'] ?></td>
                                        </tr>
                                        <?php
                                    }
                                    // for the chart
                                    // convert php variables in to javascript variables
                                    $districts_json = json_encode($districts);
                                    $counts_json = json_encode($counts);
                                } else {
                                    echo "<p class='text-primary mt-2'>No any issues recorded yet</p>";
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
                        <h3 class="card-title">Customers by District</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <!--canvas is a html 5 element-->
                            <canvas id="pieChart" style="min-height: 250px; height: 300px; max-height: 450px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <!--canvas is a html 5 element-->
                            <canvas id="barChartDistrict" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
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
        var lineChartCanvas = $('#lineChart').get(0).getContext('2d');
        var pieChartCanvas = $('#pieChart').get(0).getContext('2d');
        var barChartDistrictCanvas = $('#barChartDistrict').get(0).getContext('2d');
        var months = <?php echo $months_json; ?>;
        var amounts = <?php echo $amounts_json; ?>;
        var districts = <?php echo $districts_json; ?>;
        var counts = <?php echo $counts_json; ?>;

        // for the bar chart
        var barChartData = {
            labels: months,
            datasets: [
                {
                    label: 'Registrations per month',
                    backgroundColor: '#1dab90',
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
        
        // for the District bar chart
        var barChartDistrictData = {
            labels: districts,
            datasets: [
                {
                    label: 'Customers by Districts',
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
        // for the line chart
        var lineChartData = {
            labels: months,
            datasets: [
                {
                    label: 'Registration Trend',
                    backgroundColor: 'rgba(60,141,188,0.9)',
                    borderColor: '#ffc107',
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

        // Create the chart
        new Chart(barChartCanvas, {
            type: 'bar',
            data: barChartData,
            options: barChartOptions
        });
        // Create the chart
        new Chart(lineChartCanvas, {
            type: 'line',
            data: lineChartData,
            options: barChartOptions
        });
        // Create the district bar chart
        new Chart(barChartDistrictCanvas, {
            type: 'bar',
            data: barChartDistrictData,
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

        var pieChartData = {
            labels: districts,
            datasets: [
                {
                    label: 'Sales Amount',
                    backgroundColor: pieColors, // Array of dynamically generated colors
                    borderColor: 'rgba(255,255,255,1)', // White border color
                    data: counts
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

        // Create the chart
        new Chart(pieChartCanvas, {
            type: 'pie',
            data: pieChartData,
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