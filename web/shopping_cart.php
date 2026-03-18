<?php

session_start();
include '../function.php';
$db = dbConn();

extract($_POST);

// add to cart button is clicked from shop page
if ($_SERVER['REQUEST_METHOD'] == "POST" && $operate == 'add_cart') {
    addToCart($id); // stock id
    header('Location:shop.php');
}

// add to cart button is clicked from compare page. redirect to cart page
if ($_SERVER['REQUEST_METHOD'] == "POST" && $operate == 'add_cart_compare') {
    addToCart($id);
    header('Location:cart.php');
}

// add to cart button is clicked from product info page. redirect to same page
if ($_SERVER['REQUEST_METHOD'] == "POST" && $operate == 'add_cart_productinfo') {
    addToCart($id);
    header('Location:http://localhost/photon/web/product_info.php?pid='.$id);
}

function addToCart($id = null) {
    // $id is the common stock id of the same group
    $sql = "SELECT"
            . " item_stock.id,"
            . " item_stock.item_id,"
            . " SUM(item_stock.qty - item_stock.issued_qty) as available_qty, "
            . " items.item_name,"
            . " items.item_image,"
            . " item_stock.unit_price"
            . " FROM item_stock"
            . " INNER JOIN items ON (items.id = item_stock.item_id)"
            . " WHERE item_stock.id='$id'";
    $db = dbConn(); // have to create connection inside the function again
    $result = $db->query($sql);
    $row = $result->fetch_assoc();

    // set/update product quantity
    if (isset($_SESSION['cart']) && isset($_SESSION['cart'][$id])) { //if [$id] is set, it is already in the cart 
        $current_qty = $_SESSION['cart'][$id]['qty'] += 1;
    } else {
        $current_qty = 1;
    }

    // create cart SESSION
    $_SESSION['cart'][$id] = array(
        'stock_id' => $row['id'],
        'item_id' => $row['item_id'],
        'item_name' => $row['item_name'],
        'item_image' => $row['item_image'],
        'unit_price' => $row['unit_price'],
        'qty' => $current_qty);
}

// when update cart button clicked
if ($_SERVER['REQUEST_METHOD'] == "POST" && $operate == 'update_cart') {

    // check if the both id and qunatity are set
    if (isset($_POST['id']) && isset($_POST['qty'])) {
        $ids = $_POST['id'];
        $quantities = $_POST['qty'];

        foreach ($ids as $key => $id) {
            $qty = $quantities[$key];

            // Update the quantity in the session cart
            $_SESSION['cart'][$id]['qty'] = $qty;
        }
    }
    header('Location:cart.php');
}