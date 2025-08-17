<?php

use core\Flash;

class UpdateDrvrStatusContr extends UpdateDrvrStatus {
    private $drvrid;
    private $drvrStatus;
    private $drvrTimeStamp;
    private $drvrToken;

    public function __construct($drvrid, $drvrStatus, $drvrTimeStamp, $drvrToken) {
        $this->drvrid = $drvrid;
        $this->drvrStatus = $drvrStatus;
        $this->drvrTimeStamp = $drvrTimeStamp;
        $this->drvrToken = $drvrToken;
    }

    public function checkAndUpdateDrvrStatus() {
        if ($this->drvrStatusInvalid() === false) {
            http_response_code(401);
            echo 'Unauthorized request';
        }

        if ($this->checkDrvrTimeStamp() === false) {
            http_response_code(415);
            echo 'Not acceptable';
        }

        if ($this->checkDrvrAccess() === false) {
            http_response_code(401);
            echo 'Unauthorized access';
        }

        $this->modifyStatus($this->drvrId, $this->drvrStatus, $this->drvrTimeStamp);
    }

    private function drvrStatusInvalid() {
        $result;
        $currentStatus = $this->drvrStatus;
        function cleanStatus($drvrStatus) {
            $clean_Status = filter_var($drvrStatus, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);            
            return $clean_Status;
        }
        
        $cleanedDrvrStatus = cleanStatus(trim($currentStatus));
        if (!preg_match("/^[a-zA-Z]{10,}$/", $cleanedDrvrStatus)) {
            $result = false;
        } 
        else {
            $result = true;
        }
        return $result;
    }

    private function checkDrvrTimeStamp() {
        $result;
        $getStamp = $this->drvrTimeStamp;
        function cleanDateOfBirth($stamp) {
            $cleanStamp = filter_var($stamp, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH);
            return $cleanStamp;
        }

        if (DateTime::createFromFormat('Y-m-d H:i:s', cleanDateOfBirth($getStamp)) === false) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }

    private function checkDrvrAccess() {
        $result;
        $getToken = $this->drvrToken;
        function cleanToken($token) {
            $sanitizedToken = htmlspecialchars($token, ENT_QUOTES);
            return $sanitizedToken;
        }
        $drvrId = $_SESSION['driver_id'];
        $secretToken = $_SESSION['drvr_token'];
        if (cleanToken($getToken) !== $secretToken && !isset($drvrId)) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }
}
?>