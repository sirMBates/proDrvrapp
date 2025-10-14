<?php

use core\Database;

class UpdateAssignment {
    protected function confirmAssignment ($driverId, $orderId, $vehicleId, $assignmentStatus) {
        $db = new Database();
        $pdo = $db->connect();
        $sql = "UPDATE work_orders
                SET confirmed_assignment = :confirmed_assignment
                WHERE driver_id = :driver_id AND order_id = :order_id AND vehicle_id = :vehicle_id
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':driver_id', $driverId);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':vehicle_id', $vehicleId);
        $stmt->bindParam(':confirmed_assignment', $assignmentStatus);
        $stmt->execute();

        if (!$stmt || $stmt->rowCount() === 0) {
            return [
                'status' => 'error',
                'message' => 'There was a glitch in the matrix! Please try request again.'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Assignment confirmation updated.'
        ];
    }
}

?>