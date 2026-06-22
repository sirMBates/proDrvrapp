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
                'message' => 'I\'m sorry but there seems to be a problem. Please try again.'
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

    private function saveSharedJobNote($pdo, array $data): void {
        if (empty($data['shared_job_note'])) {
            return;
        }

        // Get customer/origin from the assignment itself
        $sql = "SELECT customer_name, origin
                FROM work_orders
                WHERE order_id = :order_id AND driver_id = :driver_id LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':order_id' => $data['order_id'],
            ':driver_id' => $data['driver_id']
        ]);

        $assignment = $stmt->fetch();

        if (!$assignment || empty($assignment['customer_name']) || empty($assignment['origin'])) {
            return;
        }

        $customerName = trim($assignment['customer_name']);
        $originAddress = trim($assignment['origin']);
        $originKey = $this->normalizeAddressKey($originAddress);

        // Check if note exist first
        $checkSql = "SELECT note_id
                    FROM driver_shared_notes
                    WHERE driver_id = :driver_id
                    AND customer_name = :customer_name
                    AND origin_address_key = :origin_key
                    AND is_active = 1 LIMIT 1";

        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([
            ':driver_id' => $data['driver_id'],
            ':customer_name' => $customerName,
            ':origin_key' => $originKey
        ]);

        $existingNote = $checkStmt->fetch();
        
        // Update existing driver note if it exists
        if ($existingNote) {
            $updateSql = "UPDATE driver_shared_notes
                    SET note_body = :note_body, updated_at = NOW()
                    WHERE note_id = :note_id";
            
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([
                ':note_body' => $data['shared_job_note'],
                ':note_id' => $existingNote['note_id']
            ]);

            return;
        }

        // Otherwise insert new note
        $insertSql = "INSERT INTO driver_shared_notes
                    (driver_id, customer_name, origin_address, origin_address_key, note_body, is_active, created_at, updated_at)
                    VALUES
                    (:driver_id, :customer_name, :origin_address, :origin_key, :note_body, 1, NOW(), NOW())";

        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
            ':driver_id' => $data['driver_id'],
            ':customer_name' => $customerName,
            ':origin_address' => $originAddress,
            ':origin_key' => $originKey,
            ':note_body' => $data['shared_job_note']
        ]);
    }

    private function normalizeAddressKey($address) {
        $address = strtolower(trim((string)$address));
        $address = preg_replace('/[^a-z0-9\s]/', '', $address);
            $address = preg_replace('/\s+/', ' ', $address);

        $replace = [
            ' street' => ' st',
            ' avenue' => ' ave',
            ' road' => ' rd',
            ' boulevard' => ' blvd',
            ' drive' => ' dr',
            ' lane' => ' ln',
            ' court' => ' ct',
            ' place' => ' pl',
            ' circle' => ' cir'
        ];

        return str_replace(array_keys($replace), array_values($replace), $address);
    }
    
    protected function modifyAssignment(array $data) {
        $db = new Database();
        $pdo = $db->connect();

        //Convert datetime-local to MYSQL DATETIME
        $actualEndTime = str_replace('T', ' ', $data['actual_end_time']) . ':00';

        $sql = "UPDATE work_orders
                SET vehicle_id = :vehicle_id, actual_drop_time = :actual_drop_time,
                actual_end_time = :actual_end_time, total_job_time = :total_job_time,
                driving_time = :driving_time, pickup_details = :pickup_details,
                destination_details = :destination_details, signature_status = :signature_status
                WHERE order_id = :order_id AND driver_id = :driver_id";

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':vehicle_id', $data['vehicle_id']);
        $stmt->bindValue(':actual_drop_time', $data['actual_drop_time']);
        $stmt->bindValue(':actual_end_time', $actualEndTime);
        $stmt->bindValue(':total_job_time', $data['total_hrs']);
        $stmt->bindValue(':driving_time', $data['driving_time']);
        $stmt->bindValue(':pickup_details', $data['pickup_details']);
        $stmt->bindValue(':destination_details', $data['destination_details']);
        $stmt->bindValue(':signature_status', $data['signature_status'] ?? null);
        $stmt->bindValue(':order_id', $data['order_id']);
        $stmt->bindValue(':driver_id', $data['driver_id']);

        $success = $stmt->execute();

        if (!$success) {
            return [
                'status' => 'error',
                'message' => 'Assignment update failed.'
            ];
        }

        $this->saveSharedJobNote($pdo, $data);
        
        return [
            'status' => 'success',
            'message' => 'Assignment updated successfully.',
            'order_id' => $data['order_id']
        ];
    }
}

?>