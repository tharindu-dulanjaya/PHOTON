<?php
ob_start();
include_once 'init.php';

$db = dbConn();
$link = "Dashboard";
$breadcrumb_item = "Dashboard";
$breadcrumb_item_active = "View";
?> 
<div class="row">
    <div class="col-6">
        <a href="<?= SYS_URL ?>inventory/add_stock.php" class="btn btn-info"><i class="fas fa-plus-circle"></i> Add New Stock</a>
        <a href="<?= SYS_URL ?>inventory/stock_history.php" class="btn btn-primary"> View Stock History</a>
        <div class="card mt-2">
            <div class="card-header">
                <h3 class="card-title">Current Stock</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <?php
                $sql = "SELECT s.qty,"
                        . "s.item_id,"
                        . "i.item_name,"
                        . "SUM(s.qty - s.issued_qty) as available_qty "
                        . "FROM item_stock s "
                        . "INNER JOIN items i on i.id=s.item_id "
                        . "GROUP BY s.item_id, s.unit_price "
                        . "ORDER BY i.item_name, s.unit_price ASC";
                $result = $db->query($sql);
                ?>
                <table class="table table-hover text-nowrap" id="myTable">
                    <thead>
                        <tr>
                            <th>Item Name</th> 
                            <th>Stock Status</th>
                            <th>Remaining Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?= $row['item_name'] ?></td>  
                                    <td>
                                        <?php
                                        if ($row['available_qty'] == 0) {
                                            echo "<span class='badge badge-danger' style='width:80%'>Out of Stock</span>";
                                        } elseif ($row['available_qty'] <= 5) {
                                            echo "<span class='badge badge-warning' style='width:80%'>Low</span>";
                                        } elseif ($row['available_qty'] < 15) {
                                            echo "<span class='badge badge-primary' style='width:80%'>Normal</span>";
                                        } else {
                                            echo "<span class='badge badge-success' style='width:80%'>Good</span>";
                                        }
                                        ?>
                                    </td>
                                    <td><?= $row['available_qty'] ?></td>
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
    <div class="col-6">
        
    </div>
</div>
<?php
$content = ob_get_clean();
include 'layouts.php';
?>