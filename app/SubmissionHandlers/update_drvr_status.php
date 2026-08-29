<?php

declare(strict_types=1);

class UpdateDrvrStatusContr extends UpdateDrvrStatus {
    private int $driverId;
    private string $drvrStatus;
    private string $drvrTimeStamp;
    private string $drvrToken;

    public function __construct(int $driverId, string $drvrStatus, string $drvrTimeStamp, string $drvrToken) {
        $this->driverId = $driverId;
        $this->drvrStatus = $drvrStatus;
        $this->drvrTimeStamp = $drvrTimeStamp;
        $this->drvrToken = $drvrToken;
    }

    public function checkAndUpdateDrvrStatus() {
        if (!$this->isDriverStatusValid()) {
            header('Content-Type: application/json');
            http_response_code(401);
            return [
                'status' => 'error',
                'message' => 'Unauthorized request'
            ];
        }

        if ($this->checkDrvrTimeStamp() === false) {
            header('Content-Type: application/json');
            http_response_code(415);
            return [
                'status' => 'error',
                'message' => 'Not acceptable'
            ];
        }

        if ($this->checkDrvrAccess() === false) {
            header('Content-Type: application/json');
            http_response_code(401);
            return [
                'status' => 'error',
                'message' => 'Unauthorized access'
            ];
        }

        return $this->processUpdateStatus($this->driverId, $this->drvrStatus, $this->drvrTimeStamp);
    }

    private function isDriverStatusValid(): bool {
        $cleanedStatus = filter_var($this->drvrStatus, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);

        return preg_match('/^[a-zA-Z ]{5,}$/', $cleanedStatus) === 1;
    }

    private function checkDrvrTimeStamp(): bool {
        $cleanedTimestamp = filter_var($this->drvrTimeStamp, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH);

        return DateTime::createFromFormat('Y-m-d H:i:s', $cleanedTimestamp) !== false;
    }

    private function checkDrvrAccess(): bool {
        $getToken = (string) ($this->drvrToken ?? '');
        $secretToken = (string) ($_SESSION['drvr_token'] ?? '');

        if ($getToken === '' || $secretToken === '') {
            return false;
        }

        return hash_equals($secretToken, $getToken);
    }
}

?>