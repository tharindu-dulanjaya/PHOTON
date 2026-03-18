<?php
ob_start();
include_once '../init.php';
$db = dbConn();

$link = "Top Selling Products";
$breadcrumb_item = "Reports";
$breadcrumb_item_active = "Chart";
?>   

<section class="content">
    <div class="container-fluid">
        <!--filter-->
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">            
            <div>
                <div class="row">
                    <div class="form-group col-md-3">
                        <?php
                        $sql = "SELECT * FROM item_category WHERE status='1'";
                        $result = $db->query($sql);
                        ?>
                        <select class="form-control" name="category_id">
                            <option value="" readonly>Filter by Machine Category</option>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                                <option value="<?= $row['id'] ?>" <?= @$_POST['category_id'] == $row['id'] ? 'selected' : '' ?>><?= $row['category_name'] ?></option>
                            <?php } ?>
                        </select>
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
                        <h3 class="card-title">Sale by Products</h3>
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
                            if (!empty($category_id)) {
                                $where .= " i.item_category = '$category_id' AND";
                            }
                            if (!empty($where)) {
                                $where = substr($where, 0, -3); // removes the last 3 characters (AND part)
                                $where = " WHERE $where"; // final complete WHERE statement
                            }
                        }

                        $sql = "SELECT i.item_name, "
                                . "SUM(oi.unit_price * oi.qty) as amt "
                                . "FROM order_items oi "
                                . "INNER JOIN orders o ON o.id = oi.order_id "
                                . "INNER JOIN items i ON i.id=oi.item_id "
                                . "$where "
                                . "GROUP BY oi.item_id ";
                        $result = $db->query($sql);
                        if ($result->num_rows > 0) {
                            ?>
                            <table class="table table-hover text-nowrap" id="summaryTable">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Total Sale</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // for the chart
                                    $items = [];
                                    $amounts = [];
                                    while ($row = $result->fetch_assoc()) {
                                        // for the chart
                                        $items[] = $row['item_name'];
                                        $amounts[] = $row['amt'];
                                        ?>
                                        <!--for table-->
                                        <tr>
                                            <td><?= $row['item_name'] ?></td>
                                            <td><?= $row['amt'] ?></td>
                                        </tr>
                                        <?php
                                    }
                                    // for the chart
                                    // convert php variables in to javascript variables
                                    $items_json = json_encode($items);
                                    $amounts_json = json_encode($amounts);
                                } else {
                                    echo "<p class='text-primary mt-2'>No records found!</p>";
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
                        <h3 class="card-title">Product Sales Summary</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="barChart" style="min-height: 250px; height: 350px; max-height: 450px; max-width: 100%;"></canvas>
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
                        <h3 class="card-title">Sale by Product Count</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>                            
                        </div>
                    </div>
                    <div class="card-body table-responsive p-2">
                        <?php
                        // filter of the above section applies to this query too
                        $sql = "SELECT i.item_name, "
                                . "SUM(oi.qty) as ItemCount "
                                . "FROM order_items oi "
                                . "INNER JOIN orders o ON o.id = oi.order_id "
                                . "INNER JOIN items i ON i.id=oi.item_id "
                                . "$where "
                                . "GROUP BY oi.item_id ";
                        $result = $db->query($sql);
                        if ($result->num_rows > 0) {
                            ?>
                            <table class="table table-hover text-nowrap" id="summaryTable">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>No. of Sales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // for the chart
                                    $items = [];
                                    $counts = [];
                                    while ($row = $result->fetch_assoc()) {
                                        // for the chart
                                        $items[] = $row['item_name'];
                                        $counts[] = $row['ItemCount'];
                                        ?>
                                        <!--for table-->
                                        <tr>
                                            <td><?= $row['item_name'] ?></td>
                                            <td><?= $row['ItemCount'] ?></td>
                                        </tr>
                                        <?php
                                    }
                                    // for the chart
                                    // convert php variables in to javascript variables
                                    $items_json = json_encode($items);
                                    $counts_json = json_encode($counts);
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
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Product Count Summary</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="pieChart" style="min-height: 250px; height: 350px; max-height: 450px; max-width: 100%;"></canvas>
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
        var items = <?php echo $items_json; ?>;
        var amounts = <?php echo $amounts_json; ?>;
        var counts = <?php echo $counts_json; ?>;

        // for the bar chart
        var barChartData = {
            labels: items,
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

        // for the pie chart
        function getRandomColor() {
            var r = Math.floor(Math.random() * 255);
            var g = Math.floor(Math.random() * 255);
            var b = Math.floor(Math.random() * 255);
            return 'rgba(' + r + ',' + g + ',' + b + ',0.9)';
        }

        // Generate dynamic colors for each segment
        var pieColors = counts.map(() => getRandomColor());

        var pieChartData = {
            labels: items,
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
        });
    });


</script>