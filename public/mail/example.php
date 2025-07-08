<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once "../../vendor/autoload.php";
$envPath ='../../.local.env';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, $envPath);
$dotenv->load();
//$dbhost = $_ENV['DB_HOST'];
//echo $dbhost;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                  //enable verbose debug output
    $mail->isSMTP();                                        //send using SMTP
    $mail->Host = $_ENV['MAIL_HOST'];                       //set the SMTP server to send through
    $mail->SMTPAuth = true;                                 //enable SMTP authentication
    $mail->Username = $_ENV['MAIL_USERNAME'];               //SMTP username
    $mail->Password = $_ENV['MAIL_PASSWORD'];               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        //ENCRYPTION_SMTPS on port 465 enable implicit tls encryption
    $mail->Port = $_ENV['MAIL_PORT'];                       //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
    //Recipients
    $mail->setFrom('bttbuscompany@gmail.com', 'Marvin Bates Jr');
    $mail->addAddress($_ENV['MAIL_RECEIVER'], 'B-T-D');         //add recipient
    //$mail->addAddress('mike@example.com', 'Mike Clout');      //name is optional
    //$mail->addAddress('shlong@example.com');
    $mail->addReplyTo($_ENV['MAIL_RECEIVER'], 'B-T-D');
    //$mail->addCC('cc@example.com');
    //$mail->addBCC('bcc@example.com');

    //attachments
    //$mail->addAttachment('/var/tmp/file.tar.gz');             //add attachments
    //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');        //optional name

    //content
    $mail->isHTML(true);                                        //set email format to HTML
    $mail->Subject = 'Checking for user access!';
    $mail->Body = '<strong>We</strong> are all connected!' ;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>