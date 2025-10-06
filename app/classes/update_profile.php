<?php

use core\Flash;

class UpdateDrvrContr extends UpdateDrvr {
    private $drvrid;
    private $password;
    private $email;
    private $birthdate;
    private $mobileNum;

    public function __construct($drvrid, $password, $email, $birthdate, $mobileNum) {
        $this->drvrid = $drvrid;
        $this->password = $password;  
        $this->email = $email;
        $this->birthdate = $birthdate;  
        $this->mobileNum = $mobileNum;  
    }

    public function changeDrvrPwd() {
        $alert = new Flash();
        if ($this->doesDrvrExist() === false) {
            $alert::setMsg('danger', 'You must be logged into your account for this change.');
            header("location: /profile?danger=not+authorize");
            exit();
        }

        if ($this->isInputEmpty() === true) {
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

    public function changeDrvrData() {
        $alert = new Flash();
        if ($this->isInputEmpty() === true) {
            $alert::setMsg('warning', 'You must enter a email, birthdate or number.');
            header("Location: /profile?warning=empty");
            exit();
        }

        if (!empty($this->email) && $this->isEmailValid() === false) {
            $alert::setMsg('warning', 'Please enter a email address.');
            header("Location: /profile?warning=invalid+email");
            exit();
        }

        if (!empty($this->birthdate) && $this->isBirthDateValid() === false) {
            $alert::setMsg('warning', 'Please re-type your birth date.');
            header("Location: /profile?warning=invalid+birthdate");
            exit();
        }

        if (!empty($this->mobileNum) && $this->isMobileNumberValid() === false) {
            $alert::setMsg('warning', 'Please enter a mobile number.');
            header("Location: /profile?warning=invalid+number");
            exit();
        }
        $this->drvrUpdateData($this->drvrid, $this->email, $this->birthdate, $this->mobileNum);
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
        if (!empty($this->password) || !empty($this->email) || !empty($this->birthdate) || !empty($this->mobileNum)) {
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

    private function isEmailValid() {
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

    private function isBirthDateValid() {
        $result;
        $getDate = $this->birthdate;
        function cleanDateOfBirth($date) {
            $cleanDob = filter_var($date, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH);
            return $cleanDob;
        }

        if (DateTime::createFromFormat('Y-m-d', cleanDateOfBirth($getDate)) === false) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }

    private function isMobileNumberValid() {
        $result;
        $mobNumber = $this->mobileNum;
        function cleanMobileNumber($number) {
            $cleanMobNum = filter_var($number, FILTER_SANITIZE_NUMBER_INT);
            return $cleanMobNum;
        }

        if (!preg_match("/^[0-9]{10}$/", cleanMobileNumber($mobNumber))) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }
}

?>