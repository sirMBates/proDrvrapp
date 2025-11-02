<?php

use core\Database;

class UpdateDrvrStatus {
    private function modifyStatus($drvrid, $drvrStatus, $drvrTimeStamp) {
        $db = new Database;
        $sql = "INSERT INTO driver_status (driver_id, current_status, status_timestamp)
                VALUES (?,?,?)";
        $stmt = $db->connect()->prepare($sql);
        $stmt->bindParam(1, $drvrid);
        $stmt->bindParam(2, $drvrStatus);
        $stmt->bindParam(3, $drvrTimeStamp);
        $stmt->execute();

        if (!$stmt) {
            return [
                'status' => 'error',
                'message' => 'Status not updated'
                /*'drvrid' => $drvrid,
                'status' => $drvrStatus,
                'timestamp' => $drvrTimeStamp*/
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Status updated!'
        ];
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