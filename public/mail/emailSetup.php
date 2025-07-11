<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once base_path("vendor/autoload.php");

$envPath = '../../.local.env';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, $envPath);
$dotenv->load();

$mail = new PHPMailer(true);
$mail->SMTPDebug = 2; //SMTP::DEBUG_SERVER
$mail->isSMTP();
$mail->SMTPAuth = true;
$mail->Host = $_ENV['MAIL_HOST'];
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = $_ENV['MAIL_PORT'];
/* Strictly in a development environment use
** use the code below for sending email without
** an SSL Certificate Authority (CA) to
** get through the SSL socket layer 
** (used in a production environment).
*/ 
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
$mail->Username = $_ENV['MAIL_USERNAME'];
$mail->Password = $_ENV['MAIL_PASSWORD'];

$mail->isHTML(true);
return $mail;