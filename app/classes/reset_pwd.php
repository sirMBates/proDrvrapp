<?php

use core\Flash;

class ResetPwdContr extends ResetPwd {
    private $token;
    private $tokenExp;

    public __construct($token, $tokenExp) {
        $this->token = $token;
        $this->tokenExp = $tokenExp;
    }

    public function isTokenExpired() {
        $alert = new Flash();
        if($this->checkTokenExpiration($this->token) === true) {
            $alert::setMsg('validate', 'Token has expired! Please click button to generate a new token.');
            header("Location: /reset?validate=expired");
            exit();
        }
    }

    public function createNewToken() {
        $alert = new Flash();
        if ($this->getNewToken($this->token, $this->tokenExp) === false) {
            $alert::setMsg('error', 'There was a problem. Please, try again!');
            header("Location: /reset?error=not+valid");
            exit();
        } else {
            $mail = require_once home_path("mail/emailSetup.php");
            $mail->setFrom('noreply@prodriver.local', 'Help Desk');
            $mail->addAddress($this->email);
            $mail->Subject = "Forget Password";
            $mail->Body = <<<END

                    Click <a href="http://prodriver.local/reset?token=$this->token_hash">here</a> to forget password.

                    END;
            try {
                $mail->send();
            } catch (Exception $e) {
                $alert = new Flash(); //remove(comment out if need to check mailer errors).
                //echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
                $alert::setMsg('danger', "Message not sent. Try again. {$mail->ErrorInfo}");
                header("Location: /forget?danger=system+error");
                exit();
            }
        }
    }
}