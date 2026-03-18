<?php
include './init.php';
extract($_GET); // to get the stockid of the product (pid)
$db = dbConn();
$sql = "SELECT item_stock.id AS stockId
                    , items.id
                    , items.item_name
                    , items.item_description
                    , items.model_no
                    , items.item_image
                    , SUM(item_stock.qty - item_stock.issued_qty) as available_qty 
                    , item_stock.unit_price
                    , item_category.category_name
                FROM
                    item_stock
                    INNER JOIN items 
                        ON (items.id = item_stock.item_id)
                    INNER JOIN item_category 
                        ON (item_category.id = items.item_category) WHERE item_stock.id='$pid'";
$result = $db->query($sql);
$row = $result->fetch_assoc();
$item_description = $row['item_description'];

$page_title = $row['category_name'];
include 'shop_header.php';
?>

<section class="product-details spad">
    <div class="container">
        <div class="row">
            <div class="col-5">
                <div class="product__details__pic">
                    <!--large product image-->
                    <div class="product__details__pic__item">
                        <img class="product__details__pic__item--large"
                             src="../uploads/products/<?= $row['item_image'] ?>" >
                    </div>

                    <!--product thumbnails-->
                    <div class="product__details__pic__slider owl-carousel">
                        <?php
                        $itemid = $row['id'];
                        $sql = "SELECT * FROM item_images WHERE ItemId = '$itemid' LIMIT 3";
                        $result = $db->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row2 = $result->fetch_assoc()) {
                                ?>
                                <img data-imgbigurl="../uploads/products/<?= $row2['ImagePath'] ?>"
                                     src="../uploads/products/<?= $row2['ImagePath'] ?>" >
                                     <?php
                                 }
                             }
                             ?>
                    </div>
                </div>
                <div class="text-center mt-5">
                    <form method="post" action="shopping_cart.php">
                        <!-- stock_id "id" and "operate" is passed through the post method to shopping_cart.php file-->
                        <input type="hidden" name="id" value="<?= $pid ?>">
                        <!--after adding to cart, redirected back to this page-->
                        <!--<?= $row['available_qty'] > 0 ? '' : 'disabled' ?>-->

                        <button type="submit" name="operate" value="add_cart_productinfo"  class="btn btn-lg btn-success"><i class="fa fa-shopping-cart"></i> Add to Cart</button> 
                    </form>
                </div>
            </div>
            <div class="col-1"></div>
            <div class="col-6">
                <div class="product__details__text">
                    <h3><?= $row['item_name'] ?></h3>
                    <?php
                    $price = $row['unit_price'];
                    $formattedPrice = number_format($price, 2);
                    ?>
                    <div class="product__details__price mt-2">Rs. <?= $formattedPrice ?></div>
                    <?php
                    $sql = "SELECT * FROM item_specs WHERE ItemId='$itemid'";
                    $result = $db->query($sql);
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        ?>
                        <ul>
                            <li><b>Capacity</b> <span><?= $row['Capacity'] ?> Kg/h</span></li>
                            <li><b>Channels</b> <span><?= $row['Channels'] ?></span></li>
                            <li><b>Compressor</b> <span><?= $row['Compressor'] ?></span></li>
                            <li><b>No. of Cameras</b> <span><?= $row['Cameras'] ?></span></li>
                            <li><b>Power</b> <span><?= $row['Power'] ?> KW</span></li>
                            <li><b>Voltage</b> <span><?= $row['Voltage'] ?></span></li>
                            <li><b>No. of Ejectors</b> <span><?= $row['Ejectors'] ?></span></li>
                            <li><b>Weight</b> <span><?= $row['Weight'] ?> Kg</span></li>
                            <li><b>Dimensions</b> <span><?= $row['Dimensions'] ?></span></li>
                        </ul>
                        <?php
                    }
                    ?>                    
                </div>
                <!--product description-->
                <div class="row mt-5">                    
                    <textarea readonly style="resize: none; height: 600px; border: none;"><?= $item_description ?></textarea>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include 'shop_footer.php';
?>
