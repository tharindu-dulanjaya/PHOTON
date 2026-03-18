<?php

session_start();
include '../init.php';
include '../../phpqrcode/qrlib.php';
$db = dbConn();

extract($_POST); // data received from the 'view_order_items' page

$qr_path = '../../qr/';
//if the qr folder is not created, it is automatically created by mkdir function
if (!file_exists($qr_path))
    mkdir($qr_path);

$errorCorrectionLevel = 'L'; // Low. we don't use H(High) since we use the laptop webcam
$matrixPointSize = 4; // can change from 1 to 5

foreach ($issued_qty as $key => $qty) {

    $issue_qty = $qty; // quantity to issue
    $item = $items[$key];
    $price = $prices[$key];

    //select the oldest stock record for the item which has available quantity (FIFO)
    while ($issue_qty > 0) { // stops when issue qty becomes zero. until that, the loop keeps running
        // COALESCE() changes NULL values to given number 
        $sql = "SELECT * "
                . "FROM item_stock "
                . "WHERE item_id = $item "
                . "AND unit_price = '$price' "
                . "AND (qty - COALESCE(issued_qty, 0)) > 0 "
                . "ORDER BY purchase_date ASC "
                . "LIMIT 1";
        $result = $db->query($sql);

        // If no more stock available, break the loop to avoid infinite loop
        if ($result->num_rows == 0) {
            break;
        }
        //if a stock record is found
        $row = $result->fetch_assoc();
        // calculate the remaining qty of the current stock record
        $remaining_qty = $row['qty'] - ($row['issued_qty'] ?? 0); // ?? assign 0 if the value is NULL
        $item_id = $row['item_id'];
        $unit_price = $row['unit_price'];
        $i_date = date('Y-m-d');

        //check whether if the quantity that needs to be issued, is lessthan or equal to the remaining qty
        if ($issue_qty <= $remaining_qty) {
            $i_qty = $issue_qty;
            $s_id = $row['id']; // exact stock_id from which the item was issued
            $sql = "UPDATE item_stock SET issued_qty = COALESCE(issued_qty, 0) + $i_qty WHERE id = $s_id";
            $db->query($sql);

            // update order_items table with the relevant issued qty for each item (new on jul 14)
            $sql = "UPDATE order_items SET issued_qty = COALESCE(issued_qty, 0) + $i_qty WHERE order_id = '$order_id' AND item_id = '$item_id'";
            $db->query($sql);

            $sql = "INSERT INTO order_items_issue(order_id, item_id, stock_id, unit_price, issued_qty, issue_date) 
                    VALUES ('$order_id', '$item_id', '$s_id', '$unit_price', '$i_qty', '$i_date')";
            $db->query($sql);

            $issue_id = $db->insert_id; // get the last inserted id of 'order_items_issue' table
            // select set of available serial numbers for the amount of issued qty
            // then insert them to 'issued_serial_numbers' table
            $sql = "SELECT SerialNumber FROM item_serial_numbers WHERE StockId='$s_id' AND Issued='0' ORDER BY Id ASC LIMIT $i_qty";
            $result = $db->query($sql);
            while ($row = $result->fetch_assoc()) { //each serial number is issued within the loop
                $s_no = $row['SerialNumber'];

                $sql2 = "UPDATE item_serial_numbers SET Issued='1' WHERE StockId='$s_id' AND SerialNumber='$s_no'";
                $db->query($sql2);

                // generate the QR for the serial number
                $data = $s_no; // data stored in the QR
                $filename = 'serial' . md5($data . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
                $filepath = $qr_path . $filename;

                //QRcode is an object oriented class. It generates a png image
                QRcode::png($data, $filepath, $errorCorrectionLevel, $matrixPointSize, 2);

                $sql3 = "INSERT INTO issued_serial_numbers(Order_Items_Issue_Id, SerialNo, QR_Image) VALUES ('$issue_id','$s_no','$filename')";
                $db->query($sql3);

                // create ticket for each machine(each serial number) for its installation                
                // have to get the customer and item details to create the ticket. find by the relevant serial number
                $sql4 = "SELECT orders.customer_id,items.item_category,items.model_no "
                        . "FROM items "
                        . "INNER JOIN order_items_issue ON order_items_issue.item_id=items.id "
                        . "INNER JOIN orders ON orders.id=order_items_issue.order_id "
                        . "INNER JOIN issued_serial_numbers ON issued_serial_numbers.Order_Items_Issue_Id=order_items_issue.id "
                        . "WHERE issued_serial_numbers.SerialNo='$s_no' "
                        . "ORDER BY order_items_issue.id DESC";
                $result4 = $db->query($sql4);
                $row4 = $result4->fetch_assoc();
                $customerid = $row4['customer_id'];
                $machine_cat_id = $row4['item_category'];
                $model_no = $row4['model_no'];
                $date = date("Y-m-d");

                // to create ticket number
                $sql5 = "SELECT TicketId FROM tickets ORDER BY TicketId DESC LIMIT 1";
                $result5 = $db->query($sql5);
                $row5 = $result5->fetch_assoc();
                $tid = $row5['TicketId'];
                $tid = $tid + 1;
                $ticket_no = 'T-' . date('y') . date('m') . date('d') . $tid;

                // CommonIssueId is set to '1'  to denote as an installation
                $sql6 = "INSERT INTO tickets(TicketNo, OpenedDate, CustomerId, OrderId, MachineCatId, ModelNo, SerialNo, CommonIssueId, Description) "
                        . "VALUES ('$ticket_no','$date','$customerid','$order_id','$machine_cat_id','$model_no','$s_no','1','Machine Installation')";
                $db->query($sql6);
            }
            $issue_qty = 0;  // all required qty for one item have been issued
        } else {//if the quantity that needs to be issued, is morethan the remaining qty of the current stock
            $i_qty = $remaining_qty;
            $s_id = $row['id'];
            $sql = "UPDATE item_stock SET issued_qty = COALESCE(issued_qty, 0) + $i_qty WHERE id = $s_id";
            $db->query($sql);

            // update order_items table with the relevant issued qty. (new on jul 14)
            $sql = "UPDATE order_items SET issued_qty = COALESCE(issued_qty, 0) + $i_qty WHERE order_id = '$order_id' AND item_id = '$item_id'";
            $db->query($sql);

            $sql = "INSERT INTO order_items_issue(order_id, item_id, stock_id, unit_price, issued_qty, issue_date) 
                    VALUES ('$order_id', '$item_id', '$s_id', '$unit_price', '$i_qty', '$i_date')";
            $db->query($sql);

            $issue_id = $db->insert_id; // get the last inserted id of 'order_items_issue' table
            // select set of available serial numbers for the amount of issued qty
            // then insert them to 'issued_serial_numbers' table
            $sql = "SELECT SerialNumber FROM item_serial_numbers WHERE StockId='$s_id' AND Issued='0' ORDER BY Id ASC LIMIT $i_qty";
            $result = $db->query($sql);
            while ($row = $result->fetch_assoc()) { //each serial number is issued within the loop
                $s_no = $row['SerialNumber'];

                $sql2 = "UPDATE item_serial_numbers SET Issued='1' WHERE StockId='$s_id' AND SerialNumber='$s_no'";
                $db->query($sql2);

                // generate the QR for the serial number
                $data = $s_no; // data stored in the QR                
                $filename = 'serial' . md5($data . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
                $filepath = $qr_path . $filename;
                QRcode::png($data, $filepath, $errorCorrectionLevel, $matrixPointSize, 2);

                $sql3 = "INSERT INTO issued_serial_numbers(Order_Items_Issue_Id, SerialNo, QR_Image) VALUES ('$issue_id','$s_no','$filename')";
                $db->query($sql3);

                // create ticket for each machine(each serial number) for its installation                
                // have to get the customer and item details to create the ticket. find by the relevant serial number
                $sql4 = "SELECT orders.customer_id,items.item_category,items.model_no "
                        . "FROM items "
                        . "INNER JOIN order_items_issue ON order_items_issue.item_id=items.id "
                        . "INNER JOIN orders ON orders.id=order_items_issue.order_id "
                        . "INNER JOIN issued_serial_numbers ON issued_serial_numbers.Order_Items_Issue_Id=order_items_issue.id "
                        . "WHERE issued_serial_numbers.SerialNo='$s_no'";
                $result4 = $db->query($sql4);
                $row4 = $result4->fetch_assoc();
                $customerid = $row4['customer_id'];
                $machine_cat_id = $row4['item_category'];
                $model_no = $row4['model_no'];
                $date = date("Y-m-d");

                // to create ticket number
                $sql5 = "SELECT TicketId FROM tickets ORDER BY TicketId DESC LIMIT 1";
                $result5 = $db->query($sql5);
                $row5 = $result5->fetch_assoc();
                $tid = $row5['TicketId'];
                $tid = $tid + 1;
                $ticket_no = 'T-' . date('y') . date('m') . date('d') . $tid;

                // CommonIssueId is set to '1'  to denote as an installation
                $sql6 = "INSERT INTO tickets(TicketNo, OpenedDate, CustomerId, OrderId, MachineCatId, ModelNo, SerialNo, CommonIssueId, Description) "
                        . "VALUES ('$ticket_no','$date','$customerid','$order_id','$machine_cat_id','$model_no','$s_no','1','Machine Installation')";
                $db->query($sql6);
            }
            $issue_qty -= $i_qty;  // subtract the issued qty from the total issue qty (remainder of the current stock)
            // again repeats the loop until all items are issued
        }
    }
    // quantity for single machine has been issued now
    // repeats until all items are issued
}
// 1->not reviewed, 2-> advance payment failed, 3-> advance received, 4-> issued
$sql = "UPDATE orders SET order_status = '4' WHERE id = '$order_id'";
$result = $db->query($sql);
if ($result) { // if issueing is successful
    // redirect with a success status
    header("Location:../orders/view_order_items.php?order_id=$order_id&status=success");
}
