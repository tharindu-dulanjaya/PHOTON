<?php

// Include the TCPDF library
require_once('../../TCPDF/tcpdf.php');

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
        $this->Cell(0, 15, 'Payment Receipt', 0, false, 'C', 0, '', 0, false, 'M', 'M');
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

// Query to fetch payment data from the table
$payment_id = 1; // Replace with dynamic ID if needed
$sql = "SELECT * FROM payments WHERE id = $payment_id";
$result = $db->query($sql);

// Fetch the payment data
$payment = $result->fetch_assoc();

// Set absolute path to save the PDF file
$save_path = __DIR__ . '/../../invoices/payment_receipt.pdf';

// Set custom page size (e.g., 210mm x 297mm)
$custom_layout = array(210, 297); // width, height
// Create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $custom_layout, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('PHOTON Technologies');
$pdf->SetTitle('Payment Receipt');
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
$pdf->SetFont('helvetica', '', 12);

// Title
$pdf->Cell(0, 10, 'Payment Receipt', 0, 1, 'C');
$pdf->Ln(10); // Line break
// Add payment details
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(50, 10, 'Receipt No:', 0, 0);
$pdf->Cell(0, 10, $payment['id'], 0, 1);

$pdf->Cell(50, 10, 'Date:', 0, 0);
$pdf->Cell(0, 10, $payment['date'], 0, 1);

$pdf->Cell(50, 10, 'Customer Name:', 0, 0);
$pdf->Cell(0, 10, $payment['customer_name'], 0, 1);

$pdf->Cell(50, 10, 'Amount:', 0, 0);
$pdf->Cell(0, 10, 'Rs.' . number_format($payment['amount'], 2), 0, 1);

$pdf->Cell(50, 10, 'Payment Method:', 0, 0);
$pdf->Cell(0, 10, $payment['payment_method'], 0, 1);

$pdf->Ln(20); // Line break
// Add thank you note
$pdf->Cell(0, 10, 'Thank you for your payment!', 0, 1, 'C');

// Output the PDF
// $pdf->Output('payment_receipt.pdf', 'I'); // D-Download   I-Inline display   F-save file on server
// Output the PDF to a file on the server
$pdf->Output($save_path, 'F');

// can echo a message or redirect to another page
echo "Payment receipt saved: <a href='../../invoices/payment_receipt.pdf'>Download</a>";

