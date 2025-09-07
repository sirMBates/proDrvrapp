<?php

use core\Flash;

class ContactHelpContr extends GetDriver {
    private $driverid;
    private $driverName;
    private $senderEmail;
    private $receiverEmail;
    private $emailMessage;

    public function __construct($driverid, $driverName, $senderEmail, $receiverEmail, $emailMessage) {
        $this->driverid = $driverid;
        $this->driverName = $driverName;
        $this->senderEmail = $senderEmail;
        $this->receiverEmail = $receiverEmail;
        $this->emailMessage = $emailMessage;
    }

    public function contactHelpDesk() {}

    private function checkDriver() {}

    private function checkEmailMessage() {}
} 
?>