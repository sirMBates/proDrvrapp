<?php

use core\Database;
use core\Flash;

class UpdateDrvrStatus {
    private function modifyStatus($drvrid, $drvrStatus, $drvrTimeStamp) {
        $db = new Database;
        $alert = new Flash();
        $sql = "UPDATE driver
                SET status = :status, status_timestamp = :status_timestamp
                WHERE driverid = :driverid";
        $stmt = $db->connect()->prepare($sql);
        $stmt->bindParam(':driverid', $drvrid);
        $stmt->bindParam(':status', $drvrStatus);
        $stmt->bindParam(':status_timestamp', $drvrTimeStamp);
        //error_log("Preparing SQL Update for Driver ID: $drvrid");
        //error_log("Status: $drvrStatus");
        //error_log("Timestamp: $drvrTimeStamp");

        $stmt->execute();

        if (!$stmt || $stmt->rowCount() === 0) {
            error_log("No rows updated. Possible invalid driver ID.");
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Status not updated',
                'driverid' => $drvrid,
                'status' => $drvrStatus,
                'timestamp' => $drvrTimeStamp
            ]);
            exit();
        }

        return [
            'success' => 'true',
            'message' => 'Status updated successfully'
        ];
    }

    protected function processUpdateStatus($drvrid, $drvrStatus, $drvrTimeStamp) {
        return $this->modifyStatus($drvrid, $drvrStatus, $drvrTimeStamp);
    }
}
?>