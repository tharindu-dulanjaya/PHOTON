<?php
include '../function.php';
$db = dbConn();
extract($_GET); // to get itemId

if ($itemId == '0') { // load all products
    $where = "WHERE items.status = '1' AND item_category.status='1'";
} else {
    $where = "WHERE items.status = '1' AND items.id='$itemId' AND item_category.status='1'";
}

$sql = "SELECT item_stock.id, "
        . "items.item_name, "
        . "items.item_image, "
        . "SUM(item_stock.qty - item_stock.issued_qty) as available_qty, "
        . "item_stock.unit_price, "
        . "items.item_category, "
        . "item_category.category_name "
        . "FROM item_stock "
        . "INNER JOIN items ON (items.id = item_stock.item_id) "
        . "INNER JOIN item_category ON (item_category.id = items.item_category) "
        . "$where "
        . "GROUP BY item_stock.item_id, item_stock.unit_price "
        . "ORDER BY item_stock.unit_price";

$result = $db->query($sql);
$rowCount = $result->num_rows; // get the count of records in the result
?>

<div class="filter__item">
    <div class="row">
        <div class="col-lg-4 col-md-5">
        </div>
        <div class="col-lg-4 col-md-4">
            <div class="filter__found">
                <h6><span><?= $rowCount ?></span>Total Products</h6>
            </div>
        </div>
        <div class="col-lg-4 col-md-3">
        </div>
    </div>
</div>
<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        ?>
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="product__item">
                <div class="product__item__pic set-bg" data-setbg="../uploads/products/<?= $row['item_image'] ?>">
                    <!-- separate css classes for stock_in and stock_out-->
                    <div class="product__stock__<?= $row['available_qty'] > 0 ? 'in' : 'out' ?>"> <?= $row['available_qty'] > 0 ? 'In Stock' : 'Out of Stock' ?> </div>
                    <ul class="product__item__pic__hover">
                        <li><form method="post" action="shopping_cart.php">
                                <!-- "id" and "operate" is passed through the post method to shopping_cart.php file-->
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" name="operate" value="add_cart" <?= $row['available_qty'] > 0 ? '' : 'disabled' ?> class="btn btn-dark"><i class="fa fa-shopping-cart"></i> Add to Cart</button> 
                                <!--A value is passed through the button to check if the button is clicked to add item to the cart-->                                                        
                            </form>
                        </li>                                        
                    </ul>
                </div>
                <div class="product__item__text">
                    <?php
                    $price = $row['unit_price'];
                    $formattedPrice = number_format($price, 2);
                    ?>  
                    <h6><a href="product_info.php?pid=<?= $row['id'] ?>"> <?= $row['item_name'] ?> </a></h6>
                    <h5><a href="product_info.php?pid=<?= $row['id'] ?>">Rs. <?= $formattedPrice ?> </a></h5>
                    <p><a href="product_info.php?pid=<?= $row['id'] ?>" class="text-dark"> <?= $row['available_qty'] ?> in Stock </a></p>
                    <?php
                    // compare checkbox is only shown with the color sorters
                    if ($row['item_category'] == 1) {
                        ?>
                        <form id="compareForm" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                            <div class="form-check">
                                <input class="form-check-input compare-checkbox" type="checkbox" name="productIds[]" id="compareProduct<?= $row['id'] ?>" value="<?= $row['id'] ?>"> 
                                <label class="form-check-label" for="compareProduct<?= $row['id'] ?>" style="cursor: pointer">Compare</label>
                            </div>
                        </form>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
} else { // no products
    echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'No products found!',
                showCloseButton: false,
                showConfirmButton: false,
                timer: 3000
                }).then(function() {
                    window.location.href = 'http://localhost/photon/web/shop.php';
                });
            </script>";
}
?>

<script src="assets/js/shoppingmain.js"></script>

