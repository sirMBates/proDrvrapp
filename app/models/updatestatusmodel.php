<?php

use core\Database;

class UpdateDrvrStatus {
    private function modifyStatus($drvrid, $drvrStatus, $drvrTimeStamp) {
        $db = new Database();
        $sql = "INSERT INTO driver_status (driver_id, current_status, status_timestamp)
                VALUES (?,?,?)";
        try {
            $pdo = $db->connect();
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $drvrid);
            $stmt->bindParam(2, $drvrStatus);
            $stmt->bindParam(3, $drvrTimeStamp);

            $stmt->execute();

            return [
                'status' => 'success',
                'message' => 'Your status has been updated!'
            ];            
        } catch (Throwable) {
            return [
                'status' => 'error',
                'message' => 'There\'s a problem back here. We\'re fixing it right away!'
            ];
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
//error_log("[DB ERROR] PDOException: " . $e->getMessage());

/* error_log("[DB DEBUG] Attempting insert:");
## error_log("Driver ID: " . var_export($drvrid, true));
## error_log("Status: " . var_export($drvrStatus, true));
## error_log("Timestamp: " . var_export($drvrTimeStamp, true));
*/
?>