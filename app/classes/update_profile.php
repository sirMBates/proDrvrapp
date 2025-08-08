<?php

use core\Flash;

class UpdateDrvrPwdContr extends UpdateDrvrPwd {
    private $drvrid;
    private $password;

    public function __construct($drvrid, $password) {
        $this->drvrid = $drvrid;
        $this->password = $password;  
    }

    public function changeDrvrPwd() {
        $alert = new Flash();
        if ($this->doesDrvrExist() === false) {
            $alert::setMsg('danger', 'You must be logged into your account for this change.');
            header("location: /profile?danger=not+authorize");
            exit();
        }

        if ($this->isInputEmpty() === false) {
            $alert::setMsg('warning', 'Please enter your new password.');
            header("Location: /profile?warning=empty");
            exit();
        }

        if ($this->isPswordValid() === false) {
            $alert::setMsg('warning', 'Please re-type your password.');
            header("Location: /profile?warning=check+password");
            exit();
        }

        $this->drvrPwdUpdate($this->drvrid, $this->password);
    }

    private function doesDrvrExist() {
        $doesExist;
        if (!$this->drvrExist($this->drvrid)) {
            $doesExist = false;
        } else {
            $doesExist = true;
        }
        return $doesExist;
    }

    private function isInputEmpty() {
        $result;
        if (empty($this->password)) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }

    private function isPswordValid() {
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

?>