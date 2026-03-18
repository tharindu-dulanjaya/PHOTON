<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Create a new PHPMailer instance
function sendEmail($recipient = null, $recipient_name = null, $subject = null, $message = null) {
    $mail = new PHPMailer(true);

    try {
        // Set mailer to use SMTP
        $mail->isSMTP();

        // Enable SMTP debugging (for testing)
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug = 0;

        // Set the hostname of the mail server
        $mail->Host = 'smtp.gmail.com';

        // Enable TLS encryption
        $mail->SMTPSecure = 'tls';

        // Set the SMTP port (465 for SSL, 587 for TLS)
        $mail->Port = 587;

        // Set your Gmail credentials
        $mail->SMTPAuth = true;
        $mail->Username = 'tharindudulanjaya@gmail.com'; // Your Gmail address
        $mail->Password = 'ywecsqwotfuninng'; // Your Gmail password
        // Set the 'from' address and recipient
        $mail->setFrom('your@gmail.com', 'PHOTON');
        $mail->addAddress($recipient, $recipient_name);

        // Set email subject and body
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->isHTML(true);
        // Send the email
        $mail->send();

        //Email has been sent successfully
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                html: '<h4>Email has been sent successfully</h4>',
                showCloseButton: false,
                showConfirmButton: false,
                timer: 3000
                });
            </script>";
    } catch (Exception $e) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Mailer Error',
                html: '<h4>'.{$mail->ErrorInfo}.'</h4>',
                showCloseButton: false,
                showConfirmButton: false,
                timer: 3000
                });
            </script>";
    }
}

// send email with attachment
function sendEmailWithAttachment($recipient = null, $recipient_name = null, $subject = null, $message = null, $attachment_path = null) {
    $mail = new PHPMailer(true);

    try {
        // Set mailer to use SMTP
        $mail->isSMTP();
        $mail->SMTPDebug = 0; // Set to 2 for detailed debugging
        // SMTP configuration (example for Gmail)
        $mail->Host = 'smtp.gmail.com';

        // Enable TLS encryption
        $mail->SMTPSecure = 'tls';

        // Set the SMTP port (465 for SSL, 587 for TLS)
        $mail->Port = 587;

        // Set your Gmail credentials
        $mail->SMTPAuth = true;
        $mail->Username = 'tharindudulanjaya@gmail.com'; // Your Gmail address
        $mail->Password = 'ywecsqwotfuninng'; // Your Gmail password
        // Set the 'from' address and recipient
        $mail->setFrom('your@gmail.com', 'PHOTON');
        $mail->addAddress($recipient, $recipient_name);

        // Set email subject and body
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->isHTML(true);

        // Attach PDF file if path provided
        if ($attachment_path !== null) {
            $mail->addAttachment($attachment_path);
        }

        // Send the email
        $mail->send();

        //echo 'Email has been sent successfully';
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
}
