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
                'message' => 'There was a glitch in the matrix! Please try your request again.'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Assignment confirmation updated.',
            'data' => [
                'driver_id' => $driverId,
                'order_id' => $orderId,
                'vehicle_id' => $vehicleId,
                'confirmed_assignment' => $assignmentStatus
            ]
        ];
    }

    protected function removeAssignment($driverId, $orderId, $vehicleId) {
        $db = new Database();
        $pdo = $db->connect();
        $sql = "DELETE FROM work_orders
                WHERE driver_id = ? AND order_id = ? AND vehicle_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $driverId);
        $stmt->bindParam(2, $orderId);
        $stmt->bindParam(3, $vehicleId);
        $success = $stmt->execute();

        if (!$success) {
            return [
                'status' => 'error',
                'message' => 'My bad! There was a problem but don\'t worry. Just try again.'
            ];
        }

        if ($stmt->rowCount() === 0) {
            return [
                'status' => 'error',
                'message' => 'I couldn\'t get rid of the assignment for some reason. Are you sure it\'s correct?'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Assignment successfully canceled!'
        ];
    } 
}

?>