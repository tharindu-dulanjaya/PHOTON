<?php
ob_start(); //output buffer start. enables to pass multiple headers in the page
$page_title = "Shopping Cart";
include 'shop_header.php';

$db = dbConn();

extract($_GET);
extract($_POST);

// remove an item from the cart
if ($_SERVER['REQUEST_METHOD'] == 'GET' && @$action == 'del') {
    $cart = $_SESSION['cart']; // temporarily get the cart session to a variable 
    unset($cart[$id]); // unset the id of the selected product. so it will be removed from cart
    $_SESSION['cart'] = $cart;  //re-assign the new cart to the session
}
if ($_SERVER['REQUEST_METHOD'] == 'GET' && @$action == 'empty') {
    $_SESSION['cart'] = array(); // assign an empty array to the cart session
}
//before applying coupon, discount is 0

$coupon_discount = 0;
// apply coupon
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'apply_coupon') {
    $sql = "SELECT next_discount FROM orders WHERE customer_id='$customer_id' AND next_coupan='$coupon' AND order_status='9'";
    $result = $db->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $coupon_discount = $row['next_discount'];
        echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Coupon Applied!',
                        showCloseButton: false,
                        showConfirmButton: false,
                        timer: 1500
                        });
                    </script>";
    } else {
        // no such coupan found
        echo "<script>
                    Swal.fire({
                        icon: 'warning',
                        title: 'No such Coupon!',
                        showCloseButton: false,
                        showConfirmButton: false,
                        timer: 1500
                        });
                    </script>";
    }
}
?>

<section class="shoping-cart spad">
    <div class="container">
        <?php
        if (!empty($_SESSION['cart'])) {
            ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="shoping__cart__table">
                        <form id="cartForm" action="shopping_cart.php" method="post">
                            <table>
                                <thead>
                                    <tr>
                                        <th class="shoping__product">Product</th>
                                        <th>Price (Rs)</th>
                                        <th>Quantity</th>
                                        <th>Amount(Rs)</th>
                                        <th class="text-end"><a href="cart.php?action=empty"><span class="icon_trash"></span> Empty Cart</a></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 0;
                                    foreach (@$_SESSION['cart'] as $key => $value) {
                                        ?>
                                        <tr>
                                            <td class="shoping__cart__item">
                                                <img src="../uploads/products/<?= $value['item_image'] ?>" width="100px" height="100px" alt="">
                                                <h5><a href="product_info.php?pid=<?= $value['stock_id'] ?>"><?= $value['item_name'] ?></a></h5>
                                            </td>
                                            <td class="shoping__cart__price"> <?= number_format($value['unit_price'], 2) ?> </td>
                                            <td class="shoping__cart__quantity">

                                                <!--hidden field to store ids of the product in order to update cart quantity-->
                                                <input type="hidden" name="id[]" value="<?= $key ?>"> 
                                                <div class="quantity">
                                                    <div class="pro-qty">
                                                        <!--maximum quantity to purchase, is the available qty-->
                                                        <?php
                                                        $item_id = $value['item_id'];
                                                        $unit_price = $value['unit_price'];
                                                        $sql = "SELECT item_stock.id, "
                                                                . "SUM(item_stock.qty - item_stock.issued_qty) as available_qty "
                                                                . "FROM item_stock "
                                                                . "WHERE item_stock.item_id='$item_id' AND item_stock.unit_price='$unit_price' "
                                                                . "GROUP BY item_stock.item_id, item_stock.unit_price ";
                                                        $result = $db->query($sql);
                                                        $row = $result->fetch_assoc();
                                                        ?>
                                                        <input type="number" class="qty-input" name="qty[]" min="1" max="<?= $row['available_qty'] ?>" value="<?= $value['qty'] ?>">
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="shoping__cart__total">
                                                <?php
                                                $amt = $value['unit_price'] * $value['qty'];
                                                $total += $amt;
                                                echo number_format($amt, 2);
                                                ?>
                                            </td>
                                            <td class="shoping__cart__item__close">
                                                <!--$key is the stock_id-->
                                                <a href="cart.php?id=<?= $key ?>&action=del"><span class="icon_close"></span></a>                                        
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <!--hidden field to update cart through shopping_cart.php-->
                            <input type="hidden" name="operate" value="update_cart">
                        </form>
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="shoping__cart__btns">
                        <a href="<?= WEB_URL ?>shop.php" class="btn btn-outline-dark"><span class="icon_cart"></span> 
                            CONTINUE SHOPPING
                        </a>

                        <!--submit the form when button is clicked-->
                        <button form="cartForm" type="submit" class="btn btn-dark float-end"><span class="icon_loading"></span>
                            Update Cart
                        </button>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="shoping__continue">
                        <div class="shoping__discount">
                            <h5>Discount Codes</h5>
                            <!--maximum quantity to purchase, is the available qty-->

                            <?php
                            if (isset($_SESSION['USERID'])) { // user have to be logged in to view his coupans
                                $userid = $_SESSION['USERID'];
                                //need to find the customer_id from the user_id
                                $sql = "SELECT * FROM customers WHERE UserId = '$userid'";
                                $result = $db->query($sql);
                                $row = $result->fetch_assoc();
                                $customer_id = $row['CustomerId'];

                                // latest fully paid completed order by the customer
                                // get the latest coupan available for the customer
                                $sql = "SELECT * FROM orders WHERE customer_id='$customer_id' AND order_status='9' ORDER BY id DESC LIMIT 1";
                                $result = $db->query($sql); 
                                if ($result->num_rows > 0) { // have completed order
                                    $row = $result->fetch_assoc();
                                    $coupon_code = $row['next_coupan']; // available coupan for this order
                                    ?>
                                    <!--button is displayed only if there is coupan available-->
                                    <button type="button" id="checkCoupon" class="btn btn-sm btn-info mb-3">Check my Coupons</button>
                                    <?php
                                }
                            }
                            ?>
                            <form id="applyCoupon" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                                <input type="text" name="coupon" id="couponField" placeholder="Enter your coupon code">
                                <input type="hidden" name="action" value="apply_coupon">
                                <input type="hidden" name="customer_id" value="<?= $customer_id ?>">
                                <button type="submit" class="site-btn">APPLY COUPON</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="shoping__checkout">
                        <h5>Cart Total</h5>
                        <ul>
                            <li>Subtotal <span>Rs. <?= number_format($total, 2) ?></span></li>
                            <li>Discount <?= $coupon_discount * 100 ?>% <span>Rs. <?= number_format($total * $coupon_discount, 2) ?></span></li>
                            <li>Total <span>Rs. <?= number_format($total - ($total * $coupon_discount), 2) ?></span></li>
                            <?php
                            // store coupan discount in a session to access in checkout
                            $_SESSION['coupon_discount'] = $coupon_discount;
                            ?>
                        </ul>
                        <!--<button id="checkout-button" class="btn btn-success" style="width: 100%">PROCEED TO CHECKOUT</button>-->
                        <a href="<?= WEB_URL ?>checkout.php" class="btn btn-success" style="width: 100%">PROCEED TO CHECKOUT</a>
                    </div>
                </div>
            </div>
            <?php
        } else {
            ?>
            <div class="col-lg-12">
                <div class="shoping__cart__btns text-center">
                    <h5>Your Cart is empty</h5><br>
                    <a href="<?= WEB_URL ?>shop.php" class="btn btn-success">CONTINUE SHOPPING</a>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</section>

<?php
include 'shop_footer.php';
?>

<script>

    $(document).ready(function () {

        // click on check my coupans button
        $('#checkCoupon').click(function () {
            var couponCode = "<?php echo $coupon_code; ?>";
            alert("Your available coupon code is: " + couponCode);
        });


        // when checkout button is clicked, verify the qty selected is not exceeding max available qty
        $('#checkout-button').on('click', function (e) {
            var isValid = true;

            // class name of quantity field
            $('.qty-input').each(function () {
                var quantity = $(this).val().trim();
                var maxQuantity = $(this).attr('max');
                if (parseInt(quantity) > parseInt(maxQuantity)) {
                    isValid = false;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Quantity cannot be greater than ' + maxQuantity,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    return false;  // break out of each loop
                }
            });
            if (isValid) {
                // if all fields are valid go to checkout page
                window.location.href = 'checkout.php';
            }
        });
    });
</script>
