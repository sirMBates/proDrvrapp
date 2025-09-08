<?php

$alert = new core\Flash();

class ContactHelpContr extends GetDriver {
    private $driverid;
    private $driverName;
    private $driverEmail;
    private $receiverEmail;
    private $emailMessage;

    public function __construct($driverid, $driverName, $driverEmail, $receiverEmail, $emailMessage) {
        $this->driverid = $driverid;
        $this->driverName = $driverName;
        $this->driverEmail = $driverEmail;
        $this->receiverEmail = $receiverEmail;
        $this->emailMessage = $emailMessage;
    }

    public function contactHelpDesk() {
        if (!$this->checkDriverExist()) {
            $alert::setMsg('danger', 'There is a problem. Please, try again!');
            header("Location: /contact?danger=name+not+match");
            exit();
        }
    }

    private isEmptyInfo(): bool {
        $driverInfo = [
            $this->driverid,
            $this->driverName,
            $this->driverEmail,
            $this->receiverEmail,
            $this->emailMessage
        ];

        foreach($driverInfo as $value) {
            if (empty($value)) {
                return false;
            } else {
                return true;
            }
        }
    }

    private function checkDriverExist(): bool {
        $driverInformation = $this->getDrvrInfo($this->driverid);
        $separateNames = explode(" ", trim($this->driverName));
        $testPattern = "/^[a-zA-Z]+$/";
        // Clean and validate each part of the name
        $cleanedNames = array_map(function ($name) use ($testPattern) {
            $clean = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);
            return preg_match($testPattern, $clean) ? $clean : null;
        }, $separateNames);
        // If any part fails validation, reject
        if (in_array(null, $cleanedNames, true)) {
            return false;
        }
        // Compare with driver information
        [$firstName, $lastName] = $cleanedNames;
        return (
            $firstName === $driverInformation['firstName'] &&
            $lastName === $driverInformation['lastName']
        );
    }

    private function checkDriverEmail() {}
    
    private function checkEmailMessage() {}
} 
?>