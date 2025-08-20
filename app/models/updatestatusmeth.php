<?php

use core\Database;

class UpdateDrvrStatus {
    private function modifyStatus($drvrid, $drvrStatus, $drvrTimeStamp) {
        $db = new Database;
        $sql = "INSERT INTO driver_status (driverid, current_status, status_timestamp)
                VALUES (:drvrid, :current_status, :status_timestamp)";
        $stmt = $db->connect()->prepare($sql);
        $stmt->bindParam(':drvrid', $drvrid);
        $stmt->bindParam(':current_status', $drvrStatus);
        $stmt->bindParam(':status_timestamp', $drvrTimeStamp);
        $stmt->execute();

        if (!$stmt || $stmt->rowCount() === 0) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Status not updated',
                'drvrid' => $drvrid,
                'status' => $drvrStatus,
                'timestamp' => $drvrTimeStamp
            ]);
            exit();
        }
    }

    protected function processUpdateStatus($drvrid, $drvrStatus, $drvrTimeStamp) {
        return $this->modifyStatus($drvrid, $drvrStatus, $drvrTimeStamp);
    }
}
//error_log("Preparing SQL Update for Driver ID: $drvrid");
//error_log("Status: $drvrStatus");
//error_log("Timestamp: $drvrTimeStamp");
//error_log("No rows updated. Possible invalid driver ID.");
?>