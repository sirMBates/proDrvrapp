<?php

$alert = new core\Flash();

class RegistrationContr extends RegistrationInformation {
    private $newCompanyId;
    private $firstname;
    private $lastname;
    private $mobileNum;
    private $birthdate;

    public function __construct($newCompanyId, $firstname, $lastname, $mobileNum, $birthdate) {
        $this->newCompanyId = $newCompanyId;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->mobileNum = $mobileNum;
        $this->birthdate = $birthdate;
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

        if (!$this->checkCompanyId()) {
            $alert::setMsg('danger', 'A problem arised! Please try again.');
            header("Location: /register?danger=failed+op+id");
            exit();
        }

        $this->addDriverDetails($this->newCompanyId, $this->firstname, $this->lastname, $this->mobileNum, $this->birthdate, $_SESSION['user_name']);
    }

    private function isEmpty() {
        $result;
        $dataEntry = [
            $this->newCompanyId,
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

    private function checkCompanyId() {
        $result;
        if (!preg_match("/^[a-zA-Z0-9\-]{1,}$/", $this->newCompanyId)) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }
}
?>