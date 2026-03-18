<!--included in dashboard-->
<?php
ob_start();
include_once 'init.php';
$db = dbConn();

$link = "Sales Summary";
$breadcrumb_item = "Reports";
$breadcrumb_item_active = "Chart";
?>   

<section class="content">
    <div class="container-fluid">        
        <div class="row">
            <div class="col-md-5">
                <!-- table -->
                <div class="card card-dark">
                    <div class="card-header">
                        <h3 class="card-title">Order Summary</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>                            
                        </div>
                    </div>
                    <div class="card-body table-responsive p-2">
                        <?php
                        $sql = "SELECT DATE_FORMAT(o.order_date, '%M') as month, "
                                . "SUM(i.unit_price * i.qty) as amt "
                                . "FROM order_items i "
                                . "INNER JOIN orders o ON o.id = i.order_id "
                                . "GROUP BY month "
                                . "ORDER BY MONTH(o.order_date)";
                        $result = $db->query($sql);
                        if ($result->num_rows > 0) {
                            ?>
                            <table class="table table-hover text-nowrap" id="summaryTable">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Total Sale</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // for the chart
                                    $months = [];
                                    $amounts = [];
                                    while ($row = $result->fetch_assoc()) {
                                        // for the chart
                                        $months[] = $row['month'];
                                        $amounts[] = $row['amt'];
                                        ?>
                                        <!--for table-->
                                        <tr>
                                            <td><?= $row['month'] ?></td>
                                            <td><?= $row['amt'] ?></td>
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
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Monthly Order Summary</h3>

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
    </div>
</section>

<?php
$content = ob_get_clean();
include 'layouts.php';
?>

<script>
    $(document).ready(function () {
        var barChartCanvas = $('#barChart').get(0).getContext('2d');
        var lineChartCanvas = $('#lineChart').get(0).getContext('2d');
        var months = <?php echo $months_json; ?>;
        var amounts = <?php echo $amounts_json; ?>;

        // for the bar chart
        var barChartData = {
            labels: months,
            datasets: [
                {
                    label: 'Sales Revenue',
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
        // for the line chart
        var lineChartData = {
            labels: months,
            datasets: [
                {
                    label: 'Revenue by Sales',
                    backgroundColor: 'rgba(60,141,188,0.9)',
                    borderColor: '#28a745',
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
        });
    });


</script>