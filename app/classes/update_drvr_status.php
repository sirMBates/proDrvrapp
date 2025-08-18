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
        /*if ($this->drvrStatusInvalid() === false) {
            http_response_code(401);
            header("Content-Type: application/json");
            echo json_encode(['error' => 'Unauthorized request']);
            exit();
        }*/

        /*if ($this->checkDrvrTimeStamp() === false) {
            http_response_code(415);
            header("Content-Type: application/json");
            echo json_encode(['error' => 'Not acceptable']);
            exit();
        }*/

        /*if ($this->checkDrvrAccess() === false) {
            http_response_code(401);
            header("Content-Type: application/json");
            echo json_encode(['error' => 'Unauthorized access']);
            exit();
        }*/

        $this->processUpdateStatus($this->drvrid, $this->drvrStatus, $this->drvrTimeStamp);
        /*header('Content-Type: application/json');
        echo json_encode($result);
        exit();*/
    }

    private function drvrStatusInvalid() {
        $result;
        $currentStatus = $this->drvrStatus;
        function cleanStatus($drvrStatus) {
            $clean_Status = filter_var($drvrStatus, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);            
            return $clean_Status;
        }
        
        $cleanedDrvrStatus = cleanStatus($currentStatus);
        error_log("Sanitized Driver Status: " . $cleanedDrvrStatus);

        if (!preg_match("/^[a-zA-Z]{5,}$/", $cleanedDrvrStatus)) {
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

        error_log("Sanitized Timestamp: " . cleanDateOfBirth($getStamp));
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
        error_log("Sanitized Token: " . cleanToken($getToken));
        error_log("Session Token: " . $secretToken);
        error_log("Driver ID in Session: " . $drvrId);

        $secretToken = $_SESSION['drvr_token'];
        if (cleanToken($getToken) !== $secretToken) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }
}
?>