<?php

$alert = new core\Flash();

class ContactHelpContr extends GetDriver {
    private $driverid;
    private $driverName;
    private $driverEmail;
    private $helpDeskEmail;
    private $emailSubject;
    private $emailMessage;

    public function __construct($driverid, $driverName, $driverEmail, $helpDeskEmail, $emailSubject, $emailMessage) {
        $this->driverid = $driverid;
        $this->driverName = $driverName;
        $this->driverEmail = $driverEmail;
        $this->helpDeskEmail = $helpDeskEmail;
        $this->emailSubject = $emailSubject;
        $this->emailMessage = $emailMessage;
    }

    public function contactHelpDesk() {
        if ($this->isEmptyInfo()) {
            $alert::setMsg('error', 'Sorry, there seems to be a problem! Please, try again.');
            header("Location: /contact?error=missing+message");
            exit();
        }

        if (!$this->checkDriverExist()) {
            $alert::setMsg('danger', 'Sorry, there seems to be a problem! Please, try again!');
            header("Location: /contact?danger=name+not+match");
            exit();
        }

        if (!$this->checkEmailAddresses()) {
            $alert::setMsg('error', 'Sorry, There seems to be a problem! Please, try again.');
            header("Location: /contact?error=email+not+accurate");
            exit();
        }

        if (!$this->checkEmailMessage()) {
            $alert::setMsg('warning', 'There\'s a problem with your message. Please re-type your message.');
            header("Location: /contact?warning=message+not+valid");
            exit();
        }
        $this->sendEmail();
    }

    private function isEmptyInfo(): bool {
        $driverInfo = [
            $this->driverid,
            $this->driverName,
            $this->driverEmail,
            $this->helpDeskEmail,
            $this->emailSubject,
            $this->emailMessage
        ];

        foreach($driverInfo as $value) {
            if (empty($value)) {
                return true;
            }
        }
        return false;
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

    private function checkEmailAddresses() {
        $result;
        $driverInformation = $this->getDrvrInfo($this->driverid);
        $emails2Test = [
            $this->driverEmail,
            $this->helpDeskEmail
        ];

        if ($emails2Test[0] !== $driverInformation['email']) {
            return false;
        }

        foreach($emails2Test as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
        }
        return $result;
    }
    
    private function checkEmailMessage() {
        $textPattern = "/^[a-zA-Z0-9\s.,!?\"'()\-\@#%$&_+=:;\/\n\r\t\p{Extended_Pictographic}]{20,300}$/u";
        $result;
        if (!preg_match($textPattern, $this->emailMessage)) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }

    private function sendEmail() {
        $mail = require_once base_path("core/emailSetup.php");
        $mail->setFrom($this->driverEmail, $this->driverName);
        $mail->addAddress($this->helpDeskEmail);
        $mail->Subject = $this->emailSubject;
        $mail->Body = $this->emailMessage;
        $mail->isHTML(false);
        try {
            $mail->send();
        } catch (Exception $e) {
            //remove(comment out if need to check mailer errors).
            //echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
            $alert::setMsg('error', "Message not sent. Try again. {$mail->ErrorInfo}");
            header("Location: /contact?error=system+error");
            exit();
        }
    }
} 
?>