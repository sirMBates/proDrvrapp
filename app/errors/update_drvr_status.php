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
            return [
                'status' => 'error',
                'message' => 'Unauthorized request'
            ];
        }

        if ($this->checkDrvrTimeStamp() === false) {
            http_response_code(415);
            return [
                'status' => 'error',
                'message' => 'Not acceptable'
            ];
        }

        if ($this->checkDrvrAccess() === false) {
            http_response_code(401);
            return [
                'status' => 'error',
                'message' => 'Unauthorized access'
            ];
        }

        $this->processUpdateStatus($this->drvrid, $this->drvrStatus, $this->drvrTimeStamp);
    }

    private function drvrStatusInvalid() {
        $result;
        $currentStatus = $this->drvrStatus;
        function cleanStatus($drvrStatus) {
            $clean_Status = filter_var($drvrStatus, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);            
            return $clean_Status;
        }
        $cleanedDrvrStatus = cleanStatus($currentStatus);
        if (!preg_match("/^[a-zA-Z ]{5,}$/", $cleanedDrvrStatus)) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }

    private function checkDrvrTimeStamp() {
        $result;
        $getStamp = $this->drvrTimeStamp;
        function cleanDrvrTimeStamp($stamp) {
            $cleanStamp = filter_var($stamp, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH);
            return $cleanStamp;
        }
        if (DateTime::createFromFormat('Y-m-d H:i:s', cleanDrvrTimeStamp($getStamp)) === false) {
            $result = false;
        } else {
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
        $secretToken = $_SESSION['drvr_token'];
        if (cleanToken($getToken) !== $secretToken) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }
}
//error_log("Sanitized Driver Status: " . $cleanedDrvrStatus);
//error_log("Sanitized Timestamp: " . cleanDateOfBirth($getStamp));
//error_log("Sanitized Token: " . cleanToken($getToken));
//error_log("Session Token: " . $secretToken);
//error_log("Driver ID in Session: " . $drvrId);
?>