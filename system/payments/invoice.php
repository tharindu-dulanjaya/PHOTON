<?php
session_start();
// Include the TCPDF library
require_once('../../TCPDF/tcpdf.php');
include '../../mail.php';

// Extend the TCPDF class to create custom Header and Footer
// MYPDF is a custom built class (not necessary)
class MYPDF extends TCPDF {

    // Page header
    public function Header() {
        // Logo
        $image_file = 'logo.png'; // Path to the logo file
        $this->Image($image_file, 10, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 20); // Bold, size 20
        // Title
        $this->Cell(0, 10, 'Order Invoice', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Include database connection
include_once '../init.php';
$db = dbConn();

$order_id = $_GET['order_id']; // get the order_id from the url

$sql = "SELECT * FROM orders o "
        . "INNER JOIN customers c ON o.customer_id = c.CustomerId "
        . "INNER JOIN order_status s ON s.StatusId = o.order_status "
        . "INNER JOIN payment_methods pm ON pm.PayMethodId = o.payment_method "
        . "WHERE o.id = '$order_id' "
        . "ORDER BY o.order_date DESC";
$result = $db->query($sql);
$row = $result->fetch_assoc();
$customer_id = $row['customer_id']; // to get customer email to send attachment
$discount_applied = $row['discount_applied'];
$file_name = 'Invoice-'.$row['RegNo']; // registration no of customer
// Set absolute path to save the PDF file
$save_path = __DIR__ . '/../../invoices/' . $file_name . '.pdf';

// Set custom page size (e.g., 210mm x 297mm)
$custom_layout = array(180, 297); // width, height
// Create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $custom_layout, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('PHOTON Technologies');
$pdf->SetTitle('Order Invoice');
$pdf->SetSubject('Invoice');

// Set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 14);

// Title
$pdf->Cell(0, 10, 'Order Details', 0, 1, 'C');
$pdf->Ln(10); // Line break
// Add payment details
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(50, 10, 'Order Number:', 0, 0);
$pdf->Cell(0, 10, $row['order_number'], 0, 1);

$pdf->Cell(50, 10, 'Order Date :', 0, 0);
$pdf->Cell(0, 10, $row['order_date'], 0, 1);

$pdf->Cell(50, 10, 'Order Status :', 0, 0);
$pdf->Cell(0, 10, $row['OrderStatus'], 0, 1);

$pdf->Cell(50, 10, 'Payment Method:', 0, 0);
$pdf->Cell(0, 10, $row['PaymentMethod'], 0, 1);

// total order value
$sql2 = "SELECT SUM(unit_price * qty) AS OrderTotal "
        . "FROM order_items "
        . "WHERE order_id='$order_id' GROUP BY order_id";
$result2 = $db->query($sql2);
$row2 = $result2->fetch_assoc();
$OrderTotal = $row2['OrderTotal'];
$OrderDiscount = $OrderTotal * $discount_applied;
$finalOrderTotal = $OrderTotal-$OrderDiscount;

$pdf->Cell(50, 10, 'Order Total:', 0, 0);
$pdf->Cell(0, 10, 'Rs. ' . number_format($OrderTotal, 2), 0, 1);

$pdf->Cell(50, 10, 'Discount '.($discount_applied*100).'% : ' , 0, 0);
$pdf->Cell(0, 10, 'Rs. ' . number_format($OrderDiscount, 2), 0, 1);

$pdf->Cell(50, 10, 'Final Order Total:', 0, 0);
$pdf->Cell(0, 10, 'Rs. ' . number_format($finalOrderTotal, 2), 0, 1);
$pdf->Ln(15);

// Title
$pdf->SetFont('helvetica', '', 14);
$pdf->Cell(0, 10, 'Payment Details', 0, 1, 'C');
$pdf->Ln(10); // Line break

$pdf->SetFont('helvetica', '', 10);

$sql3 = "SELECT * FROM order_payments p "
        . "INNER JOIN payment_methods pm ON pm.PayMethodId=p.PaymentMethod "
        . "WHERE p.OrderId='$order_id' ";
$result3 = $db->query($sql3);
if ($result3->num_rows > 0) {
    while ($row3 = $result3->fetch_assoc()) {
        $pdf->Cell(50, 10, 'Payment Date :', 0, 0);
        $pdf->Cell(0, 10, $row3['PaymentDate'], 0, 1);

        $pdf->Cell(50, 10, 'Paid Amount :', 0, 0);
        $pdf->Cell(0, 10, 'Rs. ' . number_format($row3['PaymentAmount'], 2), 0, 1);
    }
    // total of payments made
    $sql4 = "SELECT SUM(PaymentAmount) AS TotalPaid "
            . "FROM order_payments p "
            . "WHERE OrderId='$order_id' GROUP BY p.OrderId";
    $result4 = $db->query($sql4);
    if ($result4->num_rows > 0) { // payment records found
        $row4 = $result4->fetch_assoc();
        $TotalPaid = $row4['TotalPaid'];
        $Due = $finalOrderTotal - $TotalPaid;
    } else { // no payments have made yet
        $TotalPaid = 0;
        $Due = $finalOrderTotal - $TotalPaid;
    }
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Ln(10); // Line break
    $pdf->Cell(50, 10, 'Total Amount Paid :', 0, 0);
    $pdf->Cell(0, 10, 'Rs. ' . number_format($TotalPaid, 2), 0, 1);

    $pdf->Cell(50, 10, 'Due Amount :', 0, 0);
    $pdf->Cell(0, 10, 'Rs. ' . number_format($Due, 2), 0, 1);
} else {
    $pdf->Cell(0, 10, 'No payments have been made yet!', 0, 1, 'C');
}

$pdf->Ln(20); // Line break
// Add thank you note
$pdf->Cell(0, 10, 'Thank you for being an our valuable customer!', 0, 1, 'C');

// Output the PDF
// $pdf->Output('payment_receipt.pdf', 'I'); // D-Download   I-Inline display   F-save file on server
// Output the PDF to a file on the server
$pdf->Output($save_path, 'F');

$sql5 = "SELECT u.Email FROM users u INNER JOIN customers c ON c.UserId=u.UserId WHERE c.CustomerId='$customer_id'";
$result5 = $db->query($sql5);
$row5 = $result5->fetch_assoc();
$cust_email = $row5['Email'];
$msg = "<h3>Order Summary</h3>";
$msg .= "<p>Your latest payment details are attached herewith.</p>";
$pdf_file = __DIR__ . '/../../invoices/' . $file_name . '.pdf';
sendEmailWithAttachment($cust_email, "PHOTON", "Payment Invoice", $msg, $pdf_file);

// after sending email, redirect to order details page with success message
$_SESSION['invoice']='success';
header("Location:http://localhost/photon/system/orders/view_order_items.php?order_id=$order_id");
