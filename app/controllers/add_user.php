<?php
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
        if ($this->isEmpty() === false) {
            //echo "<p class='text-capitalize fs-3'>empty input</p>";
            header("Location: ../../public/views/drvrsignup.php?error=emptyinput");
            exit();
        }

        if ($this->invalidUserName() === false) {
            //echo "<p class='text-capitalize fs-3'>invalid user name</p>";
            header("Location: ../../public/views/drvrsignup.php?error=namenotvalid");
            exit();
        }

        if ($this->invalidEmail() === false) {
            //echo "<p class='text-capitalize fs-3'>invalid email</p>";
            header("Location: ../../public/views/drvrsignup.php?error=emailnotvalid");
            exit();
        }

        if ($this->invalidPsword() === false) {
            //echo "<p class='text-capitalize fs-3'>invalid password</p>";
            header("Location: ../../public/views/drvrsignup.php?error=passwordnotvalid");
            exit();
        }

        if ($this->nameExist() === false) {
            //echo "<p class='text-capitalize fs-3'>person already exist</p>";
            header("Location: ../../public/views/drvrsignup.php?error=nameexistalready");
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
        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).\S{4,6}$/", $cleanUsername)) {
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
        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z]*[A-Z])(?=.*[0-9])(?=.*[!@#\$%&\._]).\S{7,}$/", $this->password)) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }

    private function nameExist() {
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