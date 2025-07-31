<?php

$alert = new core\Flash();

class ResetPwdContr extends ResetPwd {
    private $token;

    public function __construct($token) {
        $this->token = $token;
    }

    public function isTokenExpired() {
        return $this->checkTokenandExpiration($this->token);
    }

    public function createNewToken() {
        if ($this->checkNewToken() === false) {
            $alert::setMsg('error', 'No new token! Token has been sent or already used.');
            header("Location: /forget?error=token+set+already");
            exit();
        } else {
            $mail = require_once home_path("mail/emailSetup.php");
            $mail->setFrom('noreply@prodriver.local', 'Help Desk');
            $mail->addAddress($this->email);
            $mail->Subject = "Forget Password";
            $mail->Body = <<<END

                    Click <a href="http://prodriver.local/reset?token=$this->token">here</a> to forget password.

                    END;
            try {
                $mail->send();
            } catch (Exception $e) {
                //remove(comment out if need to check mailer errors).
                //echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
                $alert::setMsg('danger', "Message not sent. Try again. {$mail->ErrorInfo}");
                header("Location: /forget?danger=system+error");
                exit();
            }
        }
    }

    private function checkNewToken() {
        $result;
        if (!$this->updateToken($this->token, $this->tokenExpTime, $this->token)) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }
}

?>