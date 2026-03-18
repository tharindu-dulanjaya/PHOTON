<?php
ob_start();
include_once '../init.php';
$db = dbConn();

$link = "Ticket Summary";
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
                    <div class="form-group col-md-4">
                        <?php
                        $sql = "SELECT model_no,item_name FROM items WHERE status='1'";
                        $result = $db->query($sql);
                        ?>
                        <select class="form-control" name="item_modelNo">
                            <option value="" readonly>Filter by Machine Name</option>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                                <option value="<?= $row['model_no'] ?>" <?= @$_POST['item_modelNo'] == $row['model_no'] ? 'selected' : '' ?>><?= $row['item_name'] ?></option>
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
                        <h3 class="card-title">Common Ticket Issues</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>                            
                        </div>
                    </div>
                    <div class="card-body table-responsive p-2">
                        <?php
                        // Id 1 is omitted (Installation is not an issue)
                        $where = null; 
                        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                            extract($_POST); // to get data from form fields
                            if (!empty($category_id)) {
                                $where .= " t.MachineCatId = '$category_id' AND";
                            }
                            if (!empty($item_modelNo)) {
                                $where .= " t.ModelNo = '$item_modelNo' AND";
                            }
                            if (!empty($where)) {
                                $where = substr($where, 0, -3); // removes the last 3 characters (AND part)
                                $where = " WHERE t.CommonIssueId != 1 AND $where"; // final complete WHERE statement
                            }
                        }else{
                            $where = "WHERE t.CommonIssueId != 1 "; // common issue id = 1 is omitted due to installation is not an issue
                        }

                        $sql = "SELECT c.Issue, "
                                . "COUNT(*) AS occurrence, "
                                . "ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM tickets t $where )) AS percentage "
                                . "FROM common_machine_issues c "
                                . "INNER JOIN tickets t ON c.Id=t.CommonIssueId "
                                . "$where "
                                . "GROUP BY t.CommonIssueId "
                                . "ORDER BY percentage DESC;";
                        $result = $db->query($sql);
                        if ($result->num_rows > 0) {
                            ?>
                            <table class="table table-hover text-nowrap" id="summaryTable">
                                <thead>
                                    <tr>
                                        <th>Common Issue</th>
                                        <th>Occurrence</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // for the chart
                                    $issues = [];
                                    $counts = [];
                                    while ($row = $result->fetch_assoc()) {
                                        // for the chart
                                        $issues[] = $row['Issue'];
                                        $counts[] = $row['occurrence'];
                                        ?>
                                        <!--for table-->
                                        <tr>
                                            <td><?= $row['Issue'] ?></td>
                                            <td><?= $row['occurrence'] ?></td>
                                            <td><?= $row['percentage'] ?>%</td>
                                        </tr>
                                        <?php
                                    }
                                    // for the chart
                                    // convert php variables in to javascript variables
                                    $issues_json = json_encode($issues);
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
            <div class="col-md-7">
                <!-- chart -->
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Issue Occurring Percentage %</h3>

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
        var pieChartCanvas = $('#pieChart').get(0).getContext('2d');
        var issues = <?php echo $issues_json; ?>;
        var counts = <?php echo $counts_json; ?>;

        function getRandomColor() {
            var r = Math.floor(Math.random() * 255);
            var g = Math.floor(Math.random() * 255);
            var b = Math.floor(Math.random() * 255);
            return 'rgba(' + r + ',' + g + ',' + b + ',0.9)';
        }

        // Generate dynamic colors for each segment
        var pieColors = counts.map(() => getRandomColor());

        var pieChartData = {
            labels: issues,
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