<?php

use core\Flash;

class AddDrvrContr extends AddedDrvr {
    private $username;
    private $email;
    private $password;
    private $firstname;
    private $lastname;
    private $mobileNum;
    private $birthdate;
    
    public function __construct($username, $email, $password, $firstname, $lastname, $mobileNum, $birthdate) {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->mobileNum = $mobileNum;
        $this->birthdate = $birthdate;  
    }

    public function addDriver() {
        $alert = new core\Flash();

        if ($this->isEmpty() === false) {
            $alert::setMsg('warning', 'Please fill in all required fields.');
            header("Location: /?warning=empty"); //emptyinput
            exit();
        }

        if ($this->invalidUserName() === false) {
            $alert::setMsg('warning', 'Please re-enter your username.');
            header("Location: /?warning=invalid"); //namenotvalid
            exit();
        }

        if ($this->invalidEmail() === false) {
            $alert::setMsg('warning', 'Please re-enter your email.');
            header("Location: /?warning=invalid"); //emailnotvalid
            exit();
        }

        if ($this->invalidPsword() === false) {
            $alert::setMsg('warning', 'Please re-enter your password.');
            header("Location: /?warning=invalid"); //passwordnotvalid
            exit();
        }

        if ($this->nameOrEmailExist() === false) {
            $alert::setMsg('danger', 'Please choose a different username or email.');
            header("Location: /?danger=exist+already"); //nameexistalready
            exit();
        }

        $this->setDriver($this->username, $this->email, $this->password, $this->firstname, $this->lastname, $this->mobileNum, $this->birthdate);
    }

    private function isEmpty() {
        $result;
        if (empty($this->username) || empty($this->email) || empty($this->password)) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }

    private function invalidUserName() {
        $result;
        $cleanUsername = filter_var($this->username, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH);
        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).\S{4,}$/", $cleanUsername)) {
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

    private function invalidPsword() {
        $result;
        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z]*[A-Z])(?=.*[0-9])(?=.*[!@#%&_]).\S{7,}$/", $this->password)) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }

    private function nameOrEmailExist() {
        $result;
        if (!$this->checkDriver($this->username, $this->email)) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }
}
?>