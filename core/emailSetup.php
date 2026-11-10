<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
$mail->SMTPDebug = 2; //SMTP::DEBUG_SERVER
$mail->Debugoutput = 'error_log';
$mail->isSMTP();
$mail->SMTPAuth = true;
$mail->Host = $_ENV['MAIL_HOST'];
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = (int) $_ENV['MAIL_PORT'];
$mail->Username = $_ENV['MAIL_USERNAME'];
$mail->Password = $_ENV['MAIL_PASSWORD'];

return $mail;

?>