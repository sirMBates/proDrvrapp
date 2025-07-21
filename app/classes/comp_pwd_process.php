<?php

use core\Flash;

class CompleteResetContr extends CompleteReset {
    private $password;
    private $token;

    public function __construct($password, $token) {
        $this->password = $password;
        $this->token = $token;
    }

    public function isTokenCleared() {
        return $this->checkTokenIsValid($this->token);
    }

    public function changeDrvrPassword() {
        $alert = new Flash();
        if ($this->isEmpty() === false) {
            $alert::setMsg('warning', 'Please enter your new password.');
            header("Location: /compreset?warning=left+blank");
            exit();
        }

        if ($this->invalidPsword() === false) {
            $alert::setMsg('danger', 'Please re-type your password.');
            header("Location: /compreset?danger=fix");
            exit();
        }

        $this->updatePassword($this->token, $this->password);
    }

    public function hasTokenCleared() {
        if ($this->clearToken() === false) {
            $alert = new Flash();
            $alert::setMsg('error', 'There was a problem. Please try reset again!');
            header("Location: /forget?error=not+cleared");
            exit();
        }
    }

    private function isEmpty() {
        $result;
        if (empty($this->password)) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }

    private function invalidPsword() {
        $result;
        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#%&_]).\S{7,}$/", $this->password)) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }
}