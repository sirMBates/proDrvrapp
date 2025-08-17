<?php

use core\Database;
use core\Flash;

class UpdateDrvrStatus {
    protected function modifyStatus($drvrid, $drvrStatus, $drvrTimeStamp) {
        $db = new Database;
        $alert = new Flash();
        $sql = "UPDATE driver
                SET status = :status, updated_status_at = :updated_status_at
                WHERE driverid = :driverid";
        $stmt = $db->connect()->prepare($sql);
        $stmt->bindParam(':drvrid', $drvrid);
        $stmt->bindParam(':status', $drvrStatus);
        $stmt->bindParam(':updated_status_at', $drvrTimeStamp);
        $stmt->execute();

        if (!$stmt || $stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Status not updated']);
            exit();
        }
    }
}
?>