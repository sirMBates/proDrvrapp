<?php

$alert = new core\Flash();

class RegProContr extends RegProInfo {
    private $firstname;
    private $lastname;
    private $mobileNum;
    private $birthdate;
    private $formToken;

    public function __construct($firstname, $lastname, $mobileNum, $birthdate, $formToken) {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->mobileNum = $mobileNum;
        $this->birthdate = $birthdate;
        $this->formToken = $formToken;
    }

    public function processProfile () {
        if ($this->isEmpty() === true) {
            $alert::setMsg('warning', 'Please fill in all required fields.');
            header("Location: /register?warning=empty"); //emptyinputs
            exit();
        }

        if ($this->drvrNameInvalid() === false) {
            $alert::setMsg('warning', 'Please re-enter your first or last name.');
            header("Location: /register?warning=invalid"); //FirstorLastnameNotValid
            exit();
        }

        if ($this->drvrInvalidMobileNumber() === false) {
            $alert::setMsg('warning', 'Please re-enter your mobile number.');
            header("Location: /register?warning=invalid"); //mobileNumberNotValid
            exit();
        }

        if ($this->drvrInvalidBirthDate() === false) {
            $alert::setMsg('warning', 'Please re-enter your date of birth.');
            header("Location: /register?warning=invalid"); //birthdateNotValid
            exit();
        }

        if ($this->enterDrvrInfo() === false) {
            $alert::setMsg('danger', 'There seems to be a problem with your submission. Please try again!');
            header("Location: /register?danger=invalid"); //userNotValid
            exit();
        }

        $this->addDriverDetails($this->firstname, $this->lastname, $this->mobileNum, $this->birthdate, $_SESSION['user_name']);
    }

    private function isEmpty() {
        $result;
        $dataEntry = [
            $this->firstname, 
            $this->lastname, 
            $this->mobileNum, 
            $this->birthdate
        ];
        function cleanData($array) {
            $clean_data = [];
            foreach($array as $value) {
                $clean_data = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);
            }
            return $clean_data;
        }
        if (empty(cleanData($dataEntry))) {
            $result = true;
        }
        else {
            $result = false;
        }
        return $result;
    }

    private function drvrNameInvalid() {
        $result;
        $drvrFullName = array($this->firstname, $this->lastname);
        function cleanFullName($array) {
            $clean_name = [];
            foreach($array as $value) {
                $clean_name = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);
            }
            return $clean_name;
        }
        
        $cleanedFullName = cleanFullName($drvrFullName);
        if (!preg_match("/^[a-zA-Z]{1,}$/", $cleanedFullName)) {
            $result = false;
        } 
        else {
            $result = true;
        }
        return $result;
    }

    private function drvrInvalidMobileNumber() {
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

    private function drvrInvalidBirthDate() {
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

    private function enterDrvrInfo() {
        $result;
        $drvrToken = $this->formToken;
        function cleanToken($token) {
            $sanitizedToken = htmlspecialchars($token, ENT_QUOTES);
            return $sanitizedToken;
        }
        $username = $_SESSION['user_name'];
        $secretToken = $_SESSION['drvr_token'];
        if (cleanToken($drvrToken) !== $secretToken && !isset($username)) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }
}
?>