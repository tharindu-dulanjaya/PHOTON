<?php

include '../../mail.php';

$msg = "<h3>Order Successful</h3>";
$msg .= "<p>Your order has been successfull.. Invoice is attached herewith.</p>";
$pdf_file = __DIR__ . '/../../invoices/payment_receipt.pdf';
sendEmailWithAttachment("tharindudulanjaya@gmail.com", "PHOTON", "Payment Invoice", $msg, $pdf_file);
