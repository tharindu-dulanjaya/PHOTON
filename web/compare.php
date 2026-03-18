<?php
ob_start(); //output buffer start. enables to pass multiple headers in the page

$page_title = "Compare Machines";
include 'shop_header.php';
$db = dbConn();
?>
<section class="product spad">
    <div class="container">
        <div class="row">
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "GET") {
                if (isset($_GET['products'])) {
                    if (empty($_GET['products'])) {
                        echo '<p>No products selected for comparison</p>';
                        return;
                    }
                    // explode -> convert string in to an array
                    // implode -> converts the array in to a string seperated by comma
                    $ids = $_GET['products']; // has the product ids as a string "1,2,3,.."
                    $sql = "SELECT item_stock.id AS stock_id, "
                            . "SUM(item_stock.qty - item_stock.issued_qty) as available_qty, "
                            . "items.*, "
                            . "item_stock.*, "
                            . "item_specs.* "
                            . "FROM item_stock "
                            . "INNER JOIN items ON items.id = item_stock.item_id "
                            . "LEFT JOIN item_specs ON item_stock.item_id = item_specs.ItemId "
                            . "WHERE item_stock.id IN ($ids) "
                            . "GROUP BY item_stock.item_id, item_stock.unit_price";
                    $result = $db->query($sql);
                    if ($result->num_rows > 0) {
                        ?>
                        <table class="table table-bordered">
                            <thead class="table-success">
                                <tr>
                                    <th>Product</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Capacity</th>
                                    <th>Channels</th>
                                    <th>Compressor</th>
                                    <th>Cameras</th>
                                    <th>Power</th>
                                    <th>Voltage</th>
                                    <th>Ejectors</th>
                                    <th>Weight</th>
                                    <th>Dimensions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>                                        
                                        <td>
                                            <img src="../uploads/products/<?= $row['item_image'] ?>" width="350px" height="auto">
                                            <div class="text-center">
                                                <form method="post" action="shopping_cart.php">
                                                    <input type="hidden" name="id" value="<?= $row['stock_id'] ?>">
                                                    <!-- removed from below code <?= $row['available_qty'] > 0 ? '' : 'disabled' ?>-->
                                                    <button type="submit" name="operate" value="add_cart_compare"  class="btn btn-sm btn-dark" ><i class="fa fa-shopping-cart"></i> Add to Cart</button> 
                                                </form>
                                                <div class="mt-3">
                                                    <a href="<?= WEB_URL ?>product_info.php?pid=<?= $row['stock_id'] ?>" target="_blank" rel="noopener noreferrer">View full details <i class="fa fa-arrow-right"></i></a>
                                                </div>
                                            </div>
                                        </td>
                                        <td><b><?= $row['item_name'] ?></b></td>
                                        <td>RS <?= $row['unit_price'] ?></td>
                                        <td><?= $row['Capacity'] ?></td>
                                        <td><?= $row['Channels'] ?></td>
                                        <td><?= $row['Compressor'] ?></td>
                                        <td><?= $row['Cameras'] ?></td>
                                        <td><?= $row['Power'] ?></td>
                                        <td><?= $row['Voltage'] ?></td>
                                        <td><?= $row['Ejectors'] ?></td>
                                        <td><?= $row['Weight'] ?></td>
                                        <td><?= $row['Dimensions'] ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
            </section>
            <?php
        } else {
            echo '<p>No matching products found</p>';
        }
    } else {
        echo '<p>No products selected for comparison</p>';
    }
}

include 'shop_footer.php';
ob_end_flush(); //must end flush the function at the very end if we use ob_start at the top.
?>