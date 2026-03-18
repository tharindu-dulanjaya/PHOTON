<?php
ob_start();
$page_title = "Order Summary";
include 'shop_header.php';
$db = dbConn();

if (!empty($_GET['order_id'])) { // order_id must be received from url

    $order_id = $_GET['order_id'];

    $sql = "SELECT * FROM order_items "
            . "INNER JOIN orders ON orders.id=order_items.order_id "
            . "INNER JOIN items ON items.id=order_items.item_id "
            . "WHERE order_items.order_id='$order_id'";
    $result = $db->query($sql);
    $mainrow = $result->fetch_assoc(); // to get details of order(payment method)
    $discount_applied = $mainrow['discount_applied'];
    ?>
    <section class="shoping-cart spad">
        <div class="container">
            <div class="row text-center">
                <div class="col-10">
                    <div class="shoping__cart__table">
                        <table>
                            <thead>
                                <tr>
                                    <th class="shoping__product">Order Items</th>
                                    <th>Unit Price (Rs.)</th>
                                    <th>Quantity</th>
                                    <th>Amount (Rs.)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = $db->query($sql); // again re-assign to $result to get order items
                                if ($result->num_rows > 0) { // order record found in the database
                                    echo "<script>
                                        Swal.fire({
                                            position: 'top-end',
                                            icon: 'success',
                                            title: '',
                                            html: '<h4>Order placed successfully!</h4>',
                                            showCloseButton: false,
                                            showConfirmButton: false,
                                            timer: 3000
                                        });
                                      </script>";

                                    $total = 0;
                                    while ($row = $result->fetch_assoc()) {
                                        ?>
                                        <tr>
                                            <td class="shoping__cart__item">
                                                <img src="../uploads/products/<?= $row['item_image'] ?>" width="100px" height="100px" alt="">
                                                <h5><?= $row['item_name'] ?></h5>
                                            </td>
                                            <td class="shoping__cart__price"><?= number_format($row['unit_price'], 2) ?> </td>
                                            <td class="shoping__cart__quantity"><b> <?= $row['qty'] ?> </b></td>
                                            <td class="shoping__cart__total">
                                                <?php
                                                $amt = $row['unit_price'] * $row['qty'];
                                                $total += $amt;
                                                echo number_format($amt, 2);
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<script>
                                        Swal.fire({
                                            icon: 'warning',
                                            title: '',
                                            html: '<h4>No order items found</h4>',
                                            showCloseButton: false,
                                            showConfirmButton: false,
                                            timer: 4000
                                        }).then(function() {
                                            window.location.href = 'shop.php';
                                        });
                                      </script>";
                                    return;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-8">
                    <?php
                    if ($mainrow['payment_method'] == 2) { // bank transfer -> layout to preview payment slip
                        ?>
                        <div class="card card-dark">
                            <div class="card-body">
                                <?php
                                // display the uploaded file if exists
                                if (!empty($mainrow['payment_slip'])) {
                                    //extract the file extension
                                    $file_ext = pathinfo($mainrow['payment_slip'], PATHINFO_EXTENSION);
                                    //check if the extension is pdf type
                                    if ($file_ext == 'pdf') {
                                        ?>
                                        <embed src="../uploads/payments/<?= $mainrow['payment_slip'] ?>" type="application/pdf" width="100%" height="550px" />
                                        <?php
                                        //check if the extension is image type
                                    } else if (in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
                                        ?>
                                        <img src="../uploads/payments/<?= $mainrow['payment_slip'] ?>" style="max-width: 100%; height: auto;" />
                                        <?php
                                    }
                                }
                                ?>                            
                            </div>
                        </div>   
                        <?php
                    }
                    ?>
                </div>
                <div class="col-4">
                    <div class="checkout__order p-5">
                        <h4>Order Details</h4>
                        <div class="checkout__order__products">Payment Method <span class="text-primary"><?= $mainrow['payment_method'] == 1 ? 'COD' : 'Bank Transfer' ?></span></div>
                        <div class="checkout__order__subtotal">Subtotal <span><?= 'Rs. '.number_format($total, 2) ?></span></div>
                        <div class="checkout__order__total">Discount <?= $discount_applied*100 ?>% <span style="color:black;">-<?= number_format($total * $discount_applied, 2) ?></span></div>
                        <div class="checkout__order__total">Total <span><?= 'Rs. '.number_format($total - ($total * $discount_applied), 2) ?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
}else{
    echo "<script>
            Swal.fire({
                icon: 'error',
                title: '',
                html: '<h4>Order details not found</h4>',
                showCloseButton: false,
                showConfirmButton: false,
                timer: 3000
            }).then(function() {
                window.location.href = 'shop.php';
            });
          </script>";
}

include 'shop_footer.php';
ob_end_flush();

