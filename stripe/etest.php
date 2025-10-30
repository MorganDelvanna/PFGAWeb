<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'shared.php';
require '../vendor/PHPMailer/src/Exception.php';
require '../vendor/PHPMailer/src/PHPMailer.php';
require '../vendor/PHPMailer/src/SMTP.php';

$mail = new PHPMailer();
// Settings
$mail->isSMTP();
$mail->CharSet = 'UTF-8';
$mail->Host = $_ENV['HOST'];
$mail->SMTPDebug = 1;
$mail->SMTPAuth = True;
$mail->Port = 25;
$mail->Username = $_ENV['USERNAME'];
$mail->Password = $_ENV['PASSWORD'];

// Content
$mail->setFrom('pfgaStripe@pfga.ca');
$mail->addAddress('dawebguy2@pfga.ca');
$mail->isHTML(true);
$mail->Subject = 'PFGA Stripe Test';
$mail->Body = 'HTML text';
$mail->AltBody = 'plain text';

if($mail->Send()) {
    $arrResult['response'] = 'success';
 } else {
     $arrResult['response'] = 'error';
     echo "There was a problem sending the form.: " . $mail->ErrorInfo;
     exit;
 }


