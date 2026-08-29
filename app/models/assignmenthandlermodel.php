<?php

use Core\Database;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Core\Flash;
use Core\Logger;

class UpdateAssignment {
    protected function confirmAssignment (string $assignContl, int $orderId, int $driverId): array {
        $db = new Database();
        $pdo = $db->connect();
        $confirmedAt = date('Y-m-d H:i:s');

        $sql = "UPDATE work_orders
                SET assignment_status = 'confirmed', confirmed_at = :confirmed_at 
                WHERE assignment_control = :assignment_control AND order_id = :order_id AND driver_id = :driver_id AND assignment_status = 'pending' AND completed_at IS NULL AND canceled_at IS NULL
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        
        $success = $stmt->execute([
            ':confirmed_at' => $confirmedAt,
            ':assignment_control' => $assignContl,
            ':order_id' => $orderId,
            ':driver_id' => $driverId
        ]);

        if (!$success) {
            return [
                'status' => 'error',
                'message' => 'The assignment could not be confirmed. Please try again.'
            ];
        }

        if ($stmt->rowCount() !== 1) {
            return [
                'status' => 'error',
                'message' => 'The assignment is unavailable or has already been processed.'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Assignment successfully confirmed.',
            'data' => [
                'assignment_control' => $assignContl,
                'order_id' => $orderId,
                'driver_id' => $driverId,
                'assignment_status' => 'confirmed',
                'confirmed_at' => $confirmedAt
            ]
        ];
    }

    protected function cancelAssignment(string $assignContl, int $orderId, int $driverId, ?string $reason = null): array {
        $db = new Database();
        $pdo = $db->connect();

        try {
            $pdo->beginTransaction();

            $fetchSql = "SELECT order_id, assignment_control, order_ref, driver_id, vehicle_id,
                        assignment_status, completed_at, canceled_at
                        FROM work_orders
                        WHERE assignment_control = :assignment_control AND order_id = :order_id AND driver_id = :driver_id
                        LIMIT 1 FOR UPDATE";

            $fetchStmt = $pdo->prepare($fetchSql);

            $fetchStmt->execute([
                ':assignment_control' => $assignContl,
                ':order_id' => $orderId,
                ':driver_id' => $driverId
            ]);

            $assignment = $fetchStmt->fetch();

            if (!$assignment) {
                $pdo->rollBack();

                return [
                    'status' => 'error',
                    'message' => 'The assignment could not be found.'
                ];
            }

            if (!empty($assignment['completed_at'])) {
                $pdo->rollBack();

                return [
                    'status' => 'error',
                    'message' => 'A completed assignment cannot be canceled.'
                ];
            }

            if (!empty($assignment['canceled_at'])) {
                $pdo->rollBack();

                return [
                    'status' => 'error',
                    'message' => 'This assignment has already been canceled.'
                ];
            }

            if ($assignment['assignment_status'] !== 'pending') {
                $pdo->rollBack();
                return [
                    'status' => 'error',
                    'message' => 'Only a pending assignment can be canceled.'
                ];
            }

            $previousStatus = $assignment['assignment_status'];

            $updateSql = "UPDATE work_orders
                        SET assignment_status = 'canceled', canceled_at = NOW(), canceled_by = :canceled_by,
                        canceled_by_role = 'driver', cancel_reason = :cancel_reason
                        WHERE assignment_control = :assignment_control AND order_id = :order_id AND driver_id = :driver_id AND assignment_status = 'pending' AND completed_at IS NULL AND canceled_at IS NULL";

            $updateStmt = $pdo->prepare($updateSql);

            $updateSaved = $updateStmt->execute([
                ':canceled_by' => $driverId,
                ':cancel_reason' => $reason,
                ':assignment_control' => $assignContl,
                ':order_id' => $orderId,
                ':driver_id' => $driverId
            ]);

            if (!$updateSaved || $updateStmt->rowCount() !== 1) {
                throw new \RuntimeException(
                    'Expected one assignment to be canceled.'
                );
            }

            $historySql = "INSERT INTO assignment_history (
                        order_id, assignment_control, order_ref, driver_id, vehicle_id, action_type, previous_status, new_status, performed_by, performed_by_role, reason)
                        VALUES (:order_id, :assignment_control, :order_ref, :driver_id, :vehicle_id, 'canceled', :previous_status, 'canceled', :performed_by, 'driver', :reason)";

            $historyStmt = $pdo->prepare($historySql);

            $historySaved = $historyStmt->execute([
                ':order_id' => $assignment['order_id'],
                ':assignment_control' => $assignment['assignment_control'],
                ':order_ref' => $assignment['order_ref'],
                ':driver_id' => $assignment['driver_id'],
                ':vehicle_id' => $assignment['vehicle_id'],
                ':previous_status' => $previousStatus,
                ':performed_by' => $driverId,
                ':reason' => $reason
            ]);

            if (!$historySaved || $historyStmt->rowCount() !== 1) {
                throw new \RuntimeException('The assignment cancellation history was not saved.');
            }

            $pdo->commit();

            return [
                'status' => 'success',
                'message' => 'Assignment successfully canceled.',
                'data' => [
                    'assignment_control' => $assignContl,
                    'order_id' => $orderId,
                    'driver_id' => $driverId,
                    'assignment_status' => 'canceled'
                ]
            ];
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $logger = new Core\Logger('D:/webapps/logs/error.log');
            $logger->error('[ASSIGNMENT CANCEL ERROR] assignment_control=' . $assignContl . ', order_id=' . $orderId . ', driver_id=' . $driverId . ', exception=' . get_class($exception) . ', message=' . $exception->getMessage());

            return [
                'status' => 'error',
                'message' => 'The assignment could not be canceled. Please try again.'
            ];
        }
    }
    
    protected function modifyAssignment(array $data) {
        $db = new Database();
        $pdo = $db->connect();
        $alert = new Flash();
        $logger = new Logger('D:/webapps/logs/error.log');

        $actualDropTimeRaw = trim((string) ($data['actual_drop_time'] ?? ''));
        $actualDropTime = $actualDropTimeRaw === '' ? null : $actualDropTimeRaw;

        //Convert datetime-local to MYSQL DATETIME
        $actualEndTimeRaw = trim((string) ($data['actual_end_time'] ?? ''));
        $actualEndTime = null;

        if ($actualEndTimeRaw !== '') {
            $normalized = str_replace('T', ' ', $actualEndTimeRaw);
            $actualEndTime = strlen($normalized) === 16 ? $normalized . ':00' : $normalized;
        }

        $totalJobTimeRaw = trim((string) ($data['total_hrs'] ?? ''));
        $totalJobTime = $totalJobTimeRaw === '' ? null : $totalJobTimeRaw;

        $setClauses = [
            'vehicle_id = :vehicle_id',
            'actual_drop_time = :actual_drop_time',
            'actual_end_time = :actual_end_time',
            'total_job_time = :total_job_time',
            'driving_time = :driving_time',
            'pickup_details = :pickup_details',
            'destination_details = :destination_details'
        ];

        $parameters = [
            ':vehicle_id' => $data['vehicle_id'],
            ':actual_drop_time' => $actualDropTime,
            ':actual_end_time' => $actualEndTime,
            ':total_job_time' => $totalJobTime,
            ':driving_time' => $data['driving_time'],
            ':pickup_details' => $data['pickup_details'],
            ':destination_details' => $data['destination_details'],
            ':assignment_control' => $data['assignment_control'],
            ':order_id' => $data['order_id'],
            ':driver_id' => $data['driver_id']
        ];

        $signatureFields = [
            'pre_signature_path',
            'pre_signature_hash',
            'pre_signature_at',
            'post_signature_path',
            'post_signature_hash',
            'post_signature_at',
            'signature_status'
        ];

        forEach ($signatureFields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $setClauses[] = "{$field} = :{$field}";
            $parameters[":{$field}"] = $data[$field];
        }

        $sql = sprintf(
            "UPDATE work_orders
            SET %s
            WHERE assignment_control = :assignment_control
            AND order_id = :order_id 
            AND driver_id = :driver_id
            AND assignment_status = 'confirmed'
            AND completed_at IS NULL
            AND canceled_at IS NULL",
            implode(', ', $setClauses)
        );

        /*$sql = "UPDATE work_orders
                SET vehicle_id = :vehicle_id, actual_drop_time = :actual_drop_time,
                actual_end_time = :actual_end_time, total_job_time = :total_job_time,
                driving_time = :driving_time, pickup_details = :pickup_details,
                destination_details = :destination_details, signature_status = :signature_status
                WHERE order_id = :order_id AND driver_id = :driver_id";*/

        $stmt = $pdo->prepare($sql);

        /*$stmt->bindValue(':vehicle_id', $data['vehicle_id']);
        $stmt->bindValue(':actual_drop_time', $data['actual_drop_time']);
        $stmt->bindValue(':actual_end_time', $actualEndTime);
        $stmt->bindValue(':total_job_time', $data['total_hrs']);
        $stmt->bindValue(':driving_time', $data['driving_time']);
        $stmt->bindValue(':pickup_details', $data['pickup_details']);
        $stmt->bindValue(':destination_details', $data['destination_details']);
        $stmt->bindValue(':signature_status', $data['signature_status'] ?? null);
        $stmt->bindValue(':assignment_control', $data['assignment_control'] ?? null);
        $stmt->bindValue(':order_id', $data['order_id']);
        $stmt->bindValue(':driver_id', $data['driver_id']);*/

        //$success = $stmt->execute();
        $success = $stmt->execute($parameters);
        $logger->info('[MODIFY RESULT] success=' . ($success ? 'true' : 'false') . ', affected_rows=' . $stmt->rowCount());

        if (!$success) {
            $alert::setMsg('error', 'The assignment update failed. Please try again.');
            header("Location: /assignments?error=update_failed&order_id=" . urlencode((string) $data['order_id']));
            exit();
        }

        if ($stmt->rowCount() === 0) {
            $verifySql = "SELECT assignment_control
                        FROM work_orders
                        WHERE assignment_control = :assignment_control AND order_id = :order_id AND driver_id = :driver_id
                        AND assignment_status = 'confirmed' AND completed_at IS NULL AND canceled_at IS NULL
                        LIMIT 1";

            $verifyStmt = $pdo->prepare($verifySql);
            $verifyStmt->execute([
                ':assignment_control' => $data['assignment_control'],
                ':order_id' => $data['order_id'],
                ':driver_id' => $data['driver_id'],
            ]);

            if(!$verifyStmt->fetchColumn()) {
                $alert::setMsg('error', 'The assignment could not be updated. It may no longer be available.');
                header("Loaction: /assignments?error=update_failed&order_id=" . urlencode((string) $data['order_id']));
                exit();
            }
        }

        $this->saveSharedJobNote($pdo, $data);
        $identitySql = "SELECT assignment_control, order_id, order_ref
                        FROM work_orders
                        WHERE assignment_control = :assignment_control AND order_id = :order_id AND driver_id = :driver_id
                        LIMIT 1";
        $identityStmt = $pdo->prepare($identitySql);
        $identityStmt->execute([
            ':assignment_control' => $data['assignment_control'],
            ':order_id' => $data['order_id'],
            ':driver_id' => $data['driver_id']
        ]);

        $updatedIdentity = $identityStmt->fetch();
        if (!$updatedIdentity) {
            $alert::setMsg('warning', 'Assignment updated, but its identity could not be retrieved.');
            return [
                'assignment_control' => $data['assignment_control'],
                'order_id' => $data['order_id'],
                'order_ref' => ''
            ];
        }
        
        return [
            'assignment_control' => $updatedIdentity['assignment_control'],
            'order_id' => $updatedIdentity['order_id'],
            'order_ref' => $updatedIdentity['order_ref']
        ];
    }

    protected function completeAssignment(array $data, bool $markCompleted = false): array {
        $db = new Database();
        $pdo = $db->connect();
        $alert = new Core\Flash();
        $logger = new Logger('D:/webapps/logs/error.log');

        // Fetch current assignment from DB
        $sql = "SELECT wo.*, u.first_name, u.last_name 
                FROM work_orders wo
                INNER JOIN users u ON u.user_id = wo.driver_id
                WHERE wo.assignment_control = :assignment_control AND wo.order_id = :order_id AND wo.driver_id = :driver_id
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([
                ':assignment_control' => $data['assignment_control'],
                ':order_id' => $data['order_id'],
                ':driver_id' => $data['driver_id']
            ]);
        } catch (\PDOException $exception) {
            $logger->error('[COMPLETE ASSIGNMENT FETCH ERROR] ' . $exception->getMessage());
            $alert::setMsg('error', 'The assignment could not be retrieved. Please try again.');
            header("Location: /assignments?error=assignment+fetch+failed");
            exit();
        }

        $current = $stmt->fetch();

        if (!$current) {
            $logger->error('[COMPLETE ASSIGNMENT] Assignment not found. ' . 'order_id=' . ($data['order_id'] ?? 'missing') . ', driver_id=' . ($data['driver_id'] ?? 'missing'));
            $alert::setMsg('error', 'Assignment not found. Contact dispatch for more details.');
            header("Location: /assignments?error=no+assignment+found");
            exit();
        }

        $logger->info('[COMPLETE ASSIGNMENT FETCHED] ' . 'order_id=' . ($current['order_id'] ?? 'missing') . ', driver_id=' . ($current['driver_id'] ?? 'missing') . ', signature_required=' . ($current['signature_required'] ?? 'missing') . ', signature_status=' . ($current['signature_status'] ?? 'missing') . ', completed_at=' . ($current['completed_at'] ?? 'NULL') . ', mark_completed=' . ($markCompleted ? 'true' : 'false'));

        if (!empty($current['completed_at']) || ($current['assignment_status'] ?? '') === 'completed') {
            $logger->info('[COMPLETE ASSIGNMENT] Assignment was already completed. ' . 'order_id=' . $current['order_id'] . ', completed_at=' . ($current['completed_at'] ?? 'missing'));
            return $current;
        }

        if (!empty($current['canceled_at']) || ($current['assignment_status'] ?? '') === 'canceled') {
            $alert::setMsg('error', 'A canceled assignment cannot be completed.');
            header('Location: /assignments?error=assignment+canceled&order_id=' . urlencode((string) $data['order_id']));
            exit();
        }

        if (($current['assignment_status'] ?? '') !== 'confirmed') {
            $alert::setMsg('error', 'The assignment must be confirmed before it can be completed.');
            header('Location: /assignments?error=assignment+not+confirmed&order_id=' . urlencode((string) $data['order_id']));
            exit();
        }

        // Mark as completed only if requested
        if (!$markCompleted) {
            return $current;
        }

        $signatureRequired = (int) ($current['signature_required'] ?? 0) === 1;
        if ($signatureRequired && ($current['signature_status'] ?? '') !== 'complete') {
            $alert::setMsg('error', 'Both required signatures must be saved before completing this assignment.');
            header("Location: /assignments?error=signatures+incomplete&order_id=" . urlencode((string) $data['order_id']));
            exit();
        }

        $completedAt = date('Y-m-d H:i:s');

        $sqlUpdate = "UPDATE work_orders 
                    SET assignment_status = 'completed', completed_at = :completed_at
                    WHERE assignment_control = :assignment_control AND order_id = :order_id AND driver_id = :driver_id
                    AND assignment_status = 'confirmed' AND canceled_at IS NULL AND completed_at IS NULL";
        $stmtUpdate = $pdo->prepare($sqlUpdate);

        try {
            $updateExecuted = $stmtUpdate->execute([
                ':completed_at' => $completedAt,
                ':assignment_control' => $data['assignment_control'],
                ':order_id' => $data['order_id'],
                ':driver_id' => $data['driver_id']
            ]);

            $affectedRows = $stmtUpdate->rowCount();

            $logger->info('[COMPLETE ASSIGNMENT UPDATE RESULT] ' . 'executed=' . ($updateExecuted ? 'true' : 'false') . ', affected_rows=' . $affectedRows . ', order_id=' . ($data['order_id'] ?? 'missing') . ', driver_id=' . ($data['driver_id'] ?? 'missing') . ', completed_at=' . $completedAt);

            if (!$updateExecuted || $affectedRows !== 1) {
                $logger->error('[COMPLETE ASSIGNMENT UPDATE ERROR] Expected one updated row, received ' . $affectedRows . '.');
                $alert::setMsg('error', 'Could not complete assignment. Please try again.');
                header("Location: /assignments?error=not+complete&order_id=" . urlencode((string) $data['order_id']));
                exit();
            }

            $verifySql = "SELECT assignment_status, completed_at FROM work_orders
                        WHERE assignment_control = :assignment_control AND order_id = :order_id AND driver_id = :driver_id
                        Limit 1";

            $verifyStmt = $pdo->prepare($verifySql);
            $verifyStmt->execute([
                ':assignment_control' => $data['assignment_control'],
                ':order_id' => $data['order_id'],
                ':driver_id' => $data['driver_id']
            ]);

            $verifiedAssignment = $verifyStmt->fetch();
            if (!$verifiedAssignment || $verifiedAssignment['assignment_status'] !== 'completed' || empty($verifiedAssignment['completed_at'])) {
                $logger->error('[COMPLETE ASSIGNMENT VERIFY ERROR] Completion state was not saved correctly. ' . 'order_id=' . $data['order_id'] . ', driver_id=' . $data['driver_id']);
                $alert::setMsg('error', 'The assignment could not be confirmed as completed.');
                header("Location: /assignments?error=completion+not+saved&order_id=" . urlencode((string) $data['order_id']));
                exit();
            }

            $saveCompletedAt = $verifiedAssignment['completed_at'];
        } catch (\PDOException $exception) {
            $logger->error('[COMPLETE ASSIGNMENT UPDATE ERROR] ' . $exception->getMessage());
            $alert::setMsg('error', 'Could not complete the assignment. Please try again.');
            header("Location: /assignments?error=not+complete&order_id=" . urlencode((string) $data['order_id']));
            exit();
        }

        $current['assignment_status'] = 'completed';
        $current['completed_at'] = $saveCompletedAt;
        $logger->info('[COMPLETE ASSIGNMENT SUCCESS] ' . 'order_id=' . $current['order_id'] . ', driver_id=' . $current['driver_id'] . ', completed_at=' . $saveCompletedAt);
        return $current;
    }

    public function completeAssignmentPublic(array $data, bool $markCompleted = true): array {
        return $this->completeAssignment($data, $markCompleted);
    }

    public function getAssignmentForExcel(array $data): array {
        $db = new Database();
        $pdo = $db->connect();
        $alert = new Core\flash();

        // Fetch assignment + driver name
        $sql = "SELECT wo.*, u.first_name, u.last_name 
                FROM work_orders wo
                INNER JOIN users u ON u.user_id = wo.driver_id
                WHERE wo.assignment_control = :assignment_control
                AND wo.order_id = :order_id
                AND wo.driver_id = :driver_id
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':assignment_control' => $data['assignment_control'],
            ':order_id' => $data['order_id'],
            ':driver_id' => $data['driver_id']
        ]);

        $assignment = $stmt->fetch();
        if (!$assignment) {
            //throw new \Exception('Assignment not found in database.');
            $alert::setMsg('error', 'Assignment not found. Contact dispatch for more details.');
            header("Location: /assignments?error=no+assignment+found");
            exit();
        }

        // Decrypt driver names
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        $firstName = Crypto::decrypt($assignment['first_name'], $key);
        $lastName  = Crypto::decrypt($assignment['last_name'], $key);
        $assignment['operator_name'] = trim($firstName . ' ' . $lastName);

        return $assignment;
    }

    protected function getAssignmentByIdentity(string $assignmentControl, int $orderId, int $driverId): ?array {
        $db = new Database();
        $pdo = $db->connect();

        $sql = "SELECT assignment_control, order_id, driver_id, assignment_status, start_date_time, signature_required, completed_at, canceled_at
        FROM work_orders
        WHERE assignment_control = :assignment_control AND order_id = :order_id AND driver_id = :driver_id
        LIMIT 1";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':assignment_control' => $assignmentControl,
            ':order_id' => $orderId,
            ':driver_id' => $driverId
        ]);

        $assignment = $stmt->fetch();
        return $assignment ?: null;
    }

    private function saveSharedJobNote($pdo, array $data): void {
        if (empty($data['shared_job_note'])) {
            return;
        }

        // Get customer/origin from the assignment itself
        $sql = "SELECT customer_name, origin
                FROM work_orders
                WHERE assignment_control = :assignment_control AND order_id = :order_id AND driver_id = :driver_id 
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':assignment_control' => $data['assignment_control'],
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
}

?>