<?php

$alert = new core\Flash();

if (session_status() !== 2) {
    session_start();
}

if (isset($_POST['reset-pswd'])) {
// Getting the EMAIL value from the form using POST method from the name attribute.
    $email = htmlspecialchars($_POST['email']);
    $token = bin2hex(random_bytes(16));
    $token_hash = hash('sha256', $token);
    $tokenExpTime = date("Y-m-d H:i:s", time() + 60 * 30); // 30 minutes expiration time
    include_once base_path("app/models/database.php");
    include_once base_path("app/models/resetpwdmeth.php");
    include_once base_path("app/classes/reset_pswd.php");
    $enterToken = new ResetPswdContr($token_hash, $tokenExpTime, $email);
    $enterToken->checkEmailandAddTokenAndExpireTime();
    /*$mail = require base_path("mail/resetemail.php");
    $mail->setFrom('bttbuscompany@gmail.com', 'Marvin Bates Jr');
    $mail->addAddress($email);
    $mail->Subject = "Password Reset";
    $mail->Body = <<<END
            Click <a href="http://prodriver.local/reset-password.php?token=$token_hash">here</a> to reset password.

            END;
            try {
                $mail->send();
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
            }*/
    $alert::setMsg('info', 'Email sent! Please check your inbox to complete the reset.');
    header("Location: /reset?info=email+sent");
    exit();
}

?>