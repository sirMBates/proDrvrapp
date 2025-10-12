<?php

use core\Flash;

class ForgetPswdContr extends ForgetPswd {
    private $token;
    private $tokenExpTime;
    private $email;

    public function __construct($token, $tokenExpTime, $email) {
        $this->token = $token;
        $this->tokenExpTime = $tokenExpTime;
        $this->email = $email;
    }

    public function addTokenAndExpireTime() {
        $alert = new Flash();
        if ($this->isEmpty() === false) {
            $alert::setMsg('warning', 'Please fill in all required fields.');
            header("Location: /forget?warning=empty"); //emptyinput
            exit();
        }

        if ($this->invalidEmail() === false) {
            $alert::setMsg('warning', 'Please re-enter your email.');
            header("Location: /forget?warning=invalid"); //emailnotvalid
            exit();
        }

        if ($this->emailExistandSend() === false) {
            $alert::setMsg('error', 'Please use the email you created the account with to continue.');
            header("Location: /forget?error=invalid");
            exit();
        }

        if ($this->tokenExistAlready() === true) {
            $alert::setMsg('info', 'Email sent already. Please check your inbox!');
            header("Location: /forget?info=sent+already");
            exit();
        }
        // Example: Store token hash and expiration time in the database for the user
        // Assuming you have a method in the parent class to handle DB operations
        $this->setForgetToken($this->token, $this->tokenExpTime, $this->email);
    }

    public function sendForgetEmail() {
        if ($this->emailExistandSend() === false) {
            $alert::setMsg('error', 'Email not sent. Please try again.');
            header('Location: /forget?error=try+again');
            exit();
        } else {
            $mail = require_once base_path("core/emailSetup.php");
            $mail->setFrom('noreply@prodriver.local', 'Help Desk');
            $mail->addAddress($this->email);
            $mail->Subject = "Forget Password";
            $mail->Body = <<<END

                    Click <a href="https://prodriver.local/reset?token=$this->token">here</a> to reset password.

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

    private function isEmpty() {
        $result;
        if (empty($this->email)) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }

    private function invalidEmail() {
        $result;
        $cleanEmail = filter_var($this->email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($cleanEmail, FILTER_VALIDATE_EMAIL)) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }

    private function emailExistandSend() {
        $result;
        if (!$this->checkEmailExist($this->email)) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }

    private function tokenExistAlready() {
        $result;
        if ($this->checkTokenExist($this->email)) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }
}
?>