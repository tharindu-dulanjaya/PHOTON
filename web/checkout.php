<?php
ob_start(); //output buffer start. enables to pass multiple headers in the page

$page_title = "Checkout";
include 'shop_header.php'; //shop_header includes the init.php(config + function.php)
$db = dbConn();

if (!isset($_SESSION['USERID'])) {
    header("Location:login.php");
    return; // to stop executing below codes
}

checkAccess('customer'); // only customers should be allowed checkout
// if the cart is empty user cannot view the checkout
if (empty($_SESSION['cart'])) {
    header("Location:shop.php");
}

// fill the billing details with the customer details
$userid = $_SESSION['USERID'];
$sql = "SELECT c.CustomerId, u.FirstName, u.LastName, u.Email, u.AddressLine1, u.AddressLine2, u.City, u.MobileNo, d.Name FROM customers c INNER JOIN users u ON c.UserId = u.UserId LEFT JOIN districts d ON u.DistrictId = d.Id WHERE u.UserId='$userid'";
$result = $db->query($sql);
$row = $result->fetch_assoc();

$customerid = $row['CustomerId']; // to send as a hidden field in POST
$name = $row['FirstName'] . ' ' . $row['LastName'];
$email = $row['Email'];
$mobile_no = $row['MobileNo'];
$address_line1 = $row['AddressLine1'];
$address_line2 = $row['AddressLine2'];
$city = $row['City'];
$district = $row['Name'];

$coupon_discount = $_SESSION['coupon_discount'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);
    $delivery_name = dataClean($delivery_name);
    $delivery_email = dataClean($delivery_email);
    $delivery_mobile_no = dataClean($delivery_mobile_no);
    $delivery_address_line1 = dataClean($delivery_address_line1);
    $delivery_address_line2 = dataClean($delivery_address_line2);
    $delivery_city = dataClean($delivery_city);
    $delivery_district = dataClean($delivery_district);

    $message = array();
    //Required validation
    if (empty($delivery_name)) {
        $message['name'] = "Please provide Name!";
    }
    if (empty($delivery_email)) {
        $message['email'] = "Please enter Email address!";
    }
    if (empty($delivery_mobile_no)) {
        $message['mobile_no'] = "Please enter Mobile number!";
    }
    if (empty($delivery_address_line1)) {
        $message['address_line1'] = "Please enter Address Line 1!";
    }
    if (empty($delivery_address_line2)) {
        $message['address_line2'] = "Please enter Address Line 2!";
    }
    if (empty($delivery_city)) {
        $message['city'] = "City should not be blank!";
    }
    if (empty($delivery_district)) {
        $message['district'] = "District should not be blank!";
    }
    if (empty($payment_method)) {
        $message['payment_method'] = "Please select your Payment method!";
    } else {
        if ($payment_method == 2) { // 2 means it is a bank transfer
            if (empty($_FILES['slip_upload']['name'])) {
                $message['payment_method'] = "Please upload the bank transfer slip!";
            }
        }
    }

    //payment slip upload
    if (!empty($_FILES['slip_upload']['name'])) {
        $file = $_FILES['slip_upload'];
        $location = "../uploads/payments";
        $uploadResult = uploadFile($file, $location);
        if ($uploadResult['upload']) {
            $bankSlip = $uploadResult['file'];
        } else {
            $error = $uploadResult['error_file'];
            $message['payment_method'] = "<br>Bank Slip upload failed : $error";
        }
    } else { // bank slip not uploaded. may be cash on delivery
        $bankSlip = 'COD';
    }

    if (empty($message)) {

        // Y -> 2024 
        // y -> 24
        $order_date = date('Y-m-d');

        // get the last record id from the table 
        $sql = "SELECT id FROM orders ORDER BY id DESC LIMIT 1";
        $result = $db->query($sql);
        $row = $result->fetch_assoc();
        $oid = $row['id'];
        $oid = $oid + 1;

        $order_number = date('y') . date('m') . date('d') . $oid;

        // coupan & discount for next order is inserted in to database with current order
        // find no of completed (fully paid)orders by the customer
        $sql = "SELECT * FROM orders WHERE customer_id='$customerid' AND order_status='9'";
        $result = $db->query($sql);
        $order_count = $result->num_rows;
        
        // function to get Discount value for next order
        function getNextOrderDiscount($order_count) {
            $db = dbConn();
            $sql = "SELECT NextOrderDiscount FROM coupon_discount WHERE OrderCount='$order_count'";
            $result = $db->query($sql);
            $row = $result->fetch_assoc();
            return $row['NextOrderDiscount'];
        }

        $next_coupan = 'PTN' . date('m') . date('d') . $oid;
        $next_discount = getNextOrderDiscount($order_count);

        $sql = "INSERT INTO orders(order_date,customer_id,delivery_name,delivery_email,delivery_phone,delivery_address1,delivery_address2,delivery_city,delivery_district,order_notes,order_total,discount_applied,next_coupan,next_discount,payment_method,payment_slip,order_number) "
        . "VALUES ('$order_date','$customerid','$delivery_name','$delivery_email','$delivery_mobile_no','$delivery_address_line1','$delivery_address_line2','$delivery_city','$delivery_district','$order_notes','$order_total','$discount_applied','$next_coupan','$next_discount','$payment_method','$bankSlip','$order_number')";
        $db->query($sql);
        // get the primary key value of the last inserted row and assign to $order_id
        $order_id = $db->insert_id;

        $cart = $_SESSION['cart'];

        foreach ($cart as $key => $value) {
            $stock_id = $value['stock_id'];
            $item_id = $value['item_id'];
            $unit_price = $value['unit_price'];
            $qty = $value['qty'];
            $sql = "INSERT INTO order_items(order_id,item_id,stock_id,unit_price,qty) VALUES ('$order_id','$item_id','$stock_id','$unit_price','$qty')";
            $db->query($sql);
        }
        $_SESSION['cart'] = array(); // empty the cart after successful checkout
        header("Location:order_success.php?order_id=" . $order_id);
    } else {
        // $message is not empty. there are errors
        echo "<script>
                    Swal.fire({
                        icon: 'warning',
                        title: 'You have some errors!',
                        showCloseButton: false,
                        showConfirmButton: false,
                        timer: 2000
                        });
                    </script>";
    }
}
?>

<!-- Checkout Section Begin -->
<section class="checkout spad">
    <div class="container">
        <div class="checkout__form">
            <h4>Billing Details</h4>

            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data" novalidate>
                <!--whole content in one row-->
                <div class="row">
                    <!--Billing & Delivery details-->
                    <div class="col-lg-8">
                        <div class="form-floating mb-3">                                    
                            <input type="text" name="name" class="form-control" id="name" placeholder="Customer Name" value="<?= @$name ?>" readonly>
                            <label for="name">Customer Name</label>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">                            
                                    <input type="email" class="form-control" name="email" id="email" placeholder="Email" value="<?= @$email ?>" readonly>
                                    <label for="email">Email Address</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">                                                                
                                    <input type="text" class="form-control" name="mobile_no" id="mobile_no" placeholder="Mobile Number" value="<?= @$mobile_no ?>" readonly>
                                    <label for="mobile_no">Mobile Number</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="address_line1" id="address_line1" placeholder="Address Line 1" value="<?= @$address_line1 ?>" readonly>
                            <label for="address_line1">Address Line 1</label>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="address_line2" id="address_line2" placeholder="Address Line 2" value="<?= @$address_line2 ?>" readonly>
                                    <label for="address_line2">Address Line 2</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">                                    
                                    <input type="text" class="form-control" name="city" id="city" placeholder="City" value="<?= @$city ?>" readonly>
                                    <label for="city">City</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="district" id="district" placeholder="District" value="<?= @$district ?>" readonly>
                                    <label for="district">District</label>
                                </div>
                            </div>
                        </div>

                        <div class="checkout__input__checkbox" style="background-color: yellow;">
                            <label for="same_as_delivery">Deliver to same Billing Address?
                                <input type="checkbox" id="same_as_delivery" name="same_as_delivery">
                                <span class="checkmark"></span>
                            </label>
                        </div><br>

                        <h4>Delivery Details</h4>
                        <div class="form-floating">                                    
                            <input type="text" name="delivery_name" class="form-control border border-1 border-dark-subtle" id="delivery_name" placeholder="Delivery Name" value="<?= @$delivery_name ?>" required>
                            <label for="delivery_name">Delivery Name</label>
                            <span class="error_span text-danger"><?= @$message['name'] ?></span><br>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">                            
                                    <input type="email" class="form-control border border-1 border-dark-subtle" name="delivery_email" id="delivery_email" placeholder="Email" value="<?= @$delivery_email ?>" required>
                                    <label for="delivery_email">Email Address</label>
                                    <span class="error_span text-danger"><?= @$message['email'] ?></span><br>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">                                                                
                                    <input type="text" class="form-control border border-1 border-dark-subtle" name="delivery_mobile_no" id="delivery_mobile_no" placeholder="Mobile Number" value="<?= @$delivery_mobile_no ?>" required>
                                    <label for="delivery_mobile_no">Mobile Number</label>
                                    <span class="error_span text-danger"><?= @$message['mobile_no'] ?></span><br>
                                </div>
                            </div>
                        </div> 
                        <div class="form-floating">
                            <input type="text" class="form-control border border-1 border-dark-subtle" name="delivery_address_line1" id="delivery_address_line1" placeholder="Address Line 1" value="<?= @$delivery_address_line1 ?>" required>
                            <label for="delivery_address_line1">Address Line 1</label>
                            <span class="error_span text-danger"><?= @$message['address_line1'] ?></span><br>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control border border-1 border-dark-subtle" name="delivery_address_line2" id="delivery_address_line2" placeholder="Address Line 2" value="<?= @$delivery_address_line2 ?>" required>
                                    <label for="delivery_address_line2">Address Line 2</label>
                                    <span class="error_span text-danger"><?= @$message['address_line2'] ?></span><br>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">                                    
                                    <input type="text" class="form-control border border-1 border-dark-subtle" name="delivery_city" id="delivery_city" placeholder="City" value="<?= @$delivery_city ?>" required>
                                    <label for="delivery_city">City</label>
                                    <span class="error_span text-danger"><?= @$message['city'] ?></span><br>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control border border-1 border-dark-subtle" name="delivery_district" id="delivery_district" placeholder="District" value="<?= @$delivery_district ?>" required>
                                    <label for="delivery_district">District</label>
                                    <span class="error_span text-danger mt-4"><?= @$message['district'] ?></span><br>
                                </div>
                            </div>
                        </div>                        

                        <div class="form-floating">                            
                            <textarea class="form-control border border-1 border-dark-subtle" id="order_notes" name="order_notes" rows="3"></textarea>
                            <label for="order_notes">Order notes</label>
                        </div>
                    </div>

                    <!--Order Details card-->
                    <div class="col-lg-4 col-md-6">
                        <div class="checkout__order">
                            <h4>Order Details</h4>
                            <div class="checkout__order__products">Products <span>Total</span></div>
                            <ul>
                                <?php
                                $total = 0;
                                foreach (@$_SESSION['cart'] as $key => $value) {
                                    ?>

                                    <li><?= $value['item_name'] ?> 
                                        <span>
                                            <?php
                                            $amt = $value['unit_price'] * $value['qty'];
                                            $total += $amt;
                                            echo 'Rs. '. number_format($amt, 2);
                                            ?>
                                        </span>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                            <div class="checkout__order__subtotal">Subtotal <span><?= 'Rs. '.number_format($total, 2) ?></span></div>
                            <div class="checkout__order__total">Discount <?= $coupon_discount * 100 ?>% <span style="color:black;">-<?= number_format($total * $coupon_discount, 2) ?></span></div>
                            <div class="checkout__order__total">Total <span><?= 'Rs. '.number_format($total - ($total * $coupon_discount), 2) ?></span></div>
                            <input type="hidden" name="order_total" value="<?= $total - ($total * $coupon_discount) ?>">

                            <h5>Payment Method</h5>
                            <div class="form-group mt-3">
                                <?php
                                $sql = "SELECT * FROM payment_methods WHERE PayMethodStatus = '1'";
                                $result = $db->query($sql);
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="<?= $row['PayMethodId'] ?>" value="<?= $row['PayMethodId'] ?>" <?= @$payment_method == $row['PayMethodId'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="<?= $row['PayMethodId'] ?>"><b> <?= $row['PaymentMethod'] ?> </b></label>
                                        </div>
                                        <?php
                                    }
                                    // if all the payment methods are disabled
                                } else {
                                    ?>
                                    <h6>No Any Payment Method Available Right Now!</h6>
                                    <?php
                                }
                                ?>

                                <!--slip upload area displayed only if bank transfer method selected-->
                                <div id="upload_slip_area" class="mt-3" style="display: none;">
                                    <p><span style="color: #dd2222">50% Advance Payment</span> in favor of,<br><br>
                                        PHOTON Technologies (Pvt) Ltd<br>
                                        A/C - 82371302<br>
                                        Bank of Ceylon - Rajagiriya<br></p>
                                    <i>Please upload your Bank Transfer Slip here</i>
                                    <div class="input-group mb-3">
                                        <input type="file" class="form-control" name="slip_upload" id="slip_upload">
                                    </div>
                                </div>
                                <div class="error_span text-danger mt-4"><?= @$message['payment_method'] ?></div><br>
                            </div>

                            <input type="hidden" name="customerid" value="<?= $customerid ?>">
                            <!--to store the applied discount in the database-->
                            <input type="hidden" name="discount_applied" value="<?= $coupon_discount ?>">
                            <button type="submit" class="site-btn">PLACE ORDER</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<?php
include 'shop_footer.php';
ob_end_flush();
?>

<script>
    // Script to copy billing details to delivery details
    document.getElementById('same_as_delivery').addEventListener('change', function () {
        if (this.checked) {
            document.getElementById('delivery_name').value = document.getElementById('name').value;
            document.getElementById('delivery_email').value = document.getElementById('email').value;
            document.getElementById('delivery_mobile_no').value = document.getElementById('mobile_no').value;
            document.getElementById('delivery_address_line1').value = document.getElementById('address_line1').value;
            document.getElementById('delivery_address_line2').value = document.getElementById('address_line2').value;
            document.getElementById('delivery_city').value = document.getElementById('city').value;
            document.getElementById('delivery_district').value = document.getElementById('district').value;
        } else {
            document.getElementById('delivery_name').value = '';
            document.getElementById('delivery_email').value = '';
            document.getElementById('delivery_mobile_no').value = '';
            document.getElementById('delivery_address_line1').value = '';
            document.getElementById('delivery_address_line2').value = '';
            document.getElementById('delivery_city').value = '';
            document.getElementById('delivery_district').value = '';
        }
    });

    // enable or disable slip upload area
    $(document).ready(function () {
        $('input[name="payment_method"]').change(function () {
            if ($(this).val() === '1') { //1 means cash on delivery
                $('#upload_slip_area').hide();
            } else if ($(this).val() === '2') { //2 means bank transfer
                $('#upload_slip_area').show();
            }
        });
    });
</script>
