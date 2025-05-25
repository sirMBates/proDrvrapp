<?php
class DrvrProfileContr extends DrvrProfileEntry {
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

    public function processDrvrProfile () {
        if ($this->isEmpty() === false) {
            //echo "<p class='text-capitalize fs-3'>empty input</p>";
            header("Location: /register?error=emptyblank");
            exit();
        }

        if ($this->drvrNameInvalid() === false) {
            //echo "<p class='text-capitalize fs-3'>name invalid</p>";
            header("Location: /register?error=nameinvalid");
            exit();
        }

        if ($this->drvrInvalidMobileNumber() === false) {
            //echo "<p class='text-capitalize fs-3'>mobile number invalid</p>";
            header("Location: /register?error=mobileinvalid");
            exit();
        }

        if ($this->drvrInvalidBirthDate() === false) {
            //echo "<p class='text-capitalize fs-3'>dob invalid</p>";
            header("Location: /register?error=dobinvalid");
            exit();
        }

        /*if ($this->cleanToken() !== $_SESSION['drvr_token']) {
            // echo "<p class='text-capitalize fs-3'>problem with form submission</p>";
            header("Location: ../../public/views/drvrinfo.php?error=problemwithsubmission");
            exit();
        }*/

        if ($this->enterDrvrInfo() === false) {
            header("Location: /register?error=usernotvalid");
            exit();
        }

        $this->addDriverDetails($this->firstname, $this->lastname, $this->mobileNum, $this->birthdate, $_SESSION['username']);
    }

    private function isEmpty() {
        $result;
        $dataEntry = [$this->firstname, $this->lastname, $this->mobileNum, $this->birthdate];
        function cleanData($array) {
            $clean_data = [];
            foreach($array as $value) {
                $clean_data = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);
            }
            return $clean_data;
        }
        if (empty(cleanData($dataEntry))) {
            $result = false;
        }
        else {
            $result = true;
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

        if (!preg_match("/^\d{10}$/", cleanMobileNumber($mobNumber))) {
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
        $drvrSpecToken = $this->formToken;
        function cleanToken($token) {
            $sanitizedToken = htmlspecialchars($token, ENT_QUOTES);
            return $sanitizedToken;
        }
        $username = $_SESSION['username'];
        $secretToken = $_SESSION['drvr_token'];
        if (cleanToken($drvrSpecToken) !== $secretToken && !isset($username)) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }
}
?>