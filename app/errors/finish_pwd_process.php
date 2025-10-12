<?php

$alert = new core\Flash();

class CompleteResetContr extends CompleteReset {
    private $token;
    private $password;

    public function __construct($token, $password) {
        $this->token = $token;
        $this->password = $password;
    }

    public function changeDrvrPassword() {
        if ($this->isEmpty() === false) {
            $alert::setMsg('warning', 'Please enter your new password.');
            header("Location: /compreset?warning=left+blank");
            exit();
        }

        if ($this->invalidPsword() === false) {
            $alert::setMsg('warning', 'Please re-type your password.');
            header("Location: /compreset?warning=fix");
            exit();
        }

        $this->updatePassword($this->token, $this->password);
    }

    public function hasTokenCleared() {
        if ($this->removedToken() === false) {
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

    private function removedToken() {
        $removed;
        if ($this->clearToken($this->token) === false) {
            $removed = false;
        } else {
            $removed = true;
        }
        return $removed;
    }
}