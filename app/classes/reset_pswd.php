<?php

use core\Flash;

class ResetPswdContr extends ResetPswd {
    private $token_hash;
    private $tokenExpTime;
    private $email;

    public function __construct($token_hash, $tokenExpTime, $email) {
        $this->token_hash = $token_hash;
        $this->tokenExpTime = $tokenExpTime;
        $this->email = $email;
    }

    public function checkEmailandAddTokenAndExpireTime() {
        if ($this->isEmpty() === false) {
            $alert = new Flash();
            $alert::setMsg('warning', 'Please fill in all required fields.');
            header("Location: /reset?warning=empty"); //emptyinput
            exit();
        }

        if ($this->invalidEmail() === false) {
            $alert = new Flash();
            $alert::setMsg('warning', 'Please re-enter your email.');
            header("Location: /reset?warning=invalid"); //emailnotvalid
            exit();
        }
        // Example: Store token hash and expiration time in the database for the user
        // Assuming you have a method in the parent class to handle DB operations
        $this->setResetToken($this->token_hash, $this->tokenExpTime, $this->email);
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
        if (!$this->checkEmail($this->email)) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }
}
?>