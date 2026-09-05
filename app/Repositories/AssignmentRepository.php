<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Core\Logger;
use Core\Database;

class AssignmentRepository {
    private PDO $pdo;
    private ?Logger $logger;

    public function __construct(?PDO $pdo = null, ?Logger $logger = null) {
        $this->pdo = $pdo ?? (new Database())->connect();
        $this->logger = $logger;
    }

    public function insertAssignment(array $data): bool|string {
        try {
            $operatorId = isset($data['operator_id']) ? trim((string) $data['operator_id']) : null;

            // Step 1: Resolve driver/user from driver_credentials
            $driverSql = "SELECT user_id
                        FROM driver_credentials
                        WHERE operator_id = :operator_id
                        LIMIT 1";
            $driverStmt = $this->pdo->prepare($driverSql);

            $driverStmt->bindValue(':operator_id', $operatorId, PDO::PARAM_STR);
            $driverStmt->execute();

            $driverFound = $driverStmt->fetch();
            if (!$driverFound) {
                $this->logger?->log("❌ FAILURE: No driver found for operator_id: {$data['operator_id']}");
                return 'driver_not_found';
            }
            $driverId = (int) $driverFound['user_id'];

            // Step 2: Pre-check for duplicate assignment
            // Prevents identical assignment records for the same vehicle, start time, driver & order ref
            $dupCheckSql = "SELECT COUNT(*) as cnt
                            FROM work_orders
                            WHERE vehicle_id = :vehicle_id
                            AND start_date_time = :start_date_time
                            AND driver_id = :driver_id
                            AND order_ref = :order_ref";
            $dupStmt = $this->pdo->prepare($dupCheckSql);

            $dupStmt->execute([
                ':vehicle_id' => $data['vehicle_id'] ?? null,
                ':start_date_time' => $data['start_date_time'] ?? null,
                ':driver_id' => $driverId,
                ':order_ref' => $data['order_ref'] ?? null
            ]);

            $exists = $dupStmt->fetchColumn();
            if ($exists > 0) {
                $this->logger?->warning("Skipped duplicate assignment - Vehicle {$data['vehicle_id']} at {$data['start_date_time']} for driver with operator id {$data['operator_id']} and order ref {$data['order_ref']}");
                return 'duplicate';
            }

            $sql = "INSERT INTO work_orders (assignment_control, order_ref, vehicle_id, driver_id, num_of_coaches, start_date_time, spot_time, leave_date_time, return_date_drop_time, actual_drop_time, end_date_time, actual_end_time, total_job_time, driving_time, origin, destination, group_name, group_leader, group_leader_mobile, customer_name, customer_phone, contact_name, contact_mobile, pickup_details, destination_details, signature_required, signature_status, pre_signature_path, post_signature_path)

                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                            ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);

            $assignmentControl = trim((string) ($data['assignment_control'] ?? ''));
            if ($assignmentControl === '') {
                $this->logger?->error('[ASSIGNMENT INSERT] Missing assignment_control.');
                return false;
            }
            
            $stmt->bindValue(1, $assignmentControl);
            $stmt->bindValue(2, $data['order_ref']);
            $stmt->bindValue(3, $data['vehicle_id'] ?? null);
            $stmt->bindValue(4, $driverId, PDO::PARAM_INT); // driver_id inserted
            $stmt->bindValue(5, $data['num_of_coaches'] ?? null);
            $stmt->bindValue(6, $data['start_date_time'] ?? null);
            $stmt->bindValue(7, $data['spot_time'] ?? null);
            $stmt->bindValue(8, $data['leave_date_time'] ?? null);
            $stmt->bindValue(9, $data['return_date_drop_time'] ?? null);
            $stmt->bindValue(10, $data['actual_drop_time'] ?? null);
            $stmt->bindValue(11, $data['end_date_time'] ?? null);
            $stmt->bindValue(12, $data['actual_end_time'] ?? null);
            $stmt->bindValue(13, $data['total_job_time'] ?? null);
            $stmt->bindValue(14, $data['driving_time'] ?? null);
            $stmt->bindValue(15, $data['origin'] ?? null);
            $stmt->bindValue(16, $data['destination'] ?? null);
            $stmt->bindValue(17, $data['group_name'] ?? null);
            $stmt->bindValue(18, $data['group_leader'] ?? null);
            $stmt->bindValue(19, $data['group_leader_mobile'] ?? null);
            $stmt->bindValue(20, $data['customer_name'] ?? null);
            $stmt->bindValue(21, $data['customer_phone'] ?? null);
            $stmt->bindValue(22, $data['contact_name'] ?? null);
            $stmt->bindValue(23, $data['contact_mobile'] ?? null);
            $stmt->bindValue(24, $data['pickup_details'] ?? null);
            $stmt->bindValue(25, $data['destination_details'] ?? null);
            $stmt->bindValue(26, $data['signature_required'] ?? 0);
            $stmt->bindValue(27, $data['signature_status'] ?? ((int) ($data['signature_required'] ?? 0) === 1 ? 'pending' : 'not-required'));
            $stmt->bindValue(28, $data['pre_signature_path'] ?? null);
            $stmt->bindValue(29, $data['post_signature_path'] ?? null);
            $dataInserted = $stmt->execute();

            if ($dataInserted) {
                $this->logger?->info("✅ SUCCESS: Inserted assignment: {$assignmentControl} for operator ID {$data['operator_id']} assigned to vehicle {$data['vehicle_id']} at {$data['start_date_time']} with order ref {$data['order_ref']}");
                return true;
            } else {
                $this->logger?->error("❌ Assignment Insert FAILURE: Vehicle {$data['vehicle_id']} at {$data['start_date_time']} - Execute returned false");
                return false;
            }
            
        } catch (\PDOException $e) {
            $this->logger?->error("❌ Assignment Insert FAILURE: Vehicle {$data['vehicle_id']} at {$data['start_date_time']} - Error: " . $e->getMessage());
            return false;
        }
    }

    public function findActiveAssignmentsByDriver(int $driverId): array {
        $sql = "SELECT wo.*, dc.operator_id, u.first_name, u.last_name, u.birth_date
                FROM work_orders wo INNER JOIN users u ON wo.driver_id = u.user_id
                INNER JOIN driver_credentials dc ON dc.user_id = u.user_id
                WHERE wo.driver_id = :driver_id AND wo.completed_at IS NULL AND wo.canceled_at IS NULL AND wo.assignment_status <> 'canceled'
                ORDER BY wo.start_date_time ASC, wo.order_id ASC";
        $stmt = $this->pdo->prepare($sql);

        $executed = $stmt->execute([
            ':driver_id' => $driverId
        ]);

        if (!$executed) {
            throw new \RuntimeException('Assignment query failed.');
        }

        return $stmt->fetchAll();
    }

    public function findByIdentity(int $orderId, int $driverId, string $assignmentControl): ?array {
        $sql = "SELECT order_id, assignment_control, driver_id, assignment_status, confirmed_at, canceled_at, completed_at
                FROM work_orders
                WHERE order_id = :order_id
                AND driver_id = :driver_id
                AND assignment_control = :assignment_control
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);

        $executed = $stmt->execute([
            ':order_id' => $orderId,
            ':driver_id' => $driverId,
            ':assignment_control' => $assignmentControl
        ]);

        if (!$executed) {
            throw new \RuntimeException('Assignment lookup failed.');
        }

        $assignment = $stmt->fetch();

        return $assignment !== false ? $assignment : null;
    }

    public function hasBlockingAssignmentsForEOS(int $driverId, string $dayStart, string $nextDayStart): bool {
        $sql = "SELECT 1
                FROM work_orders
                WHERE driver_id = :driver_id
                AND start_date_time >= :day_start
                AND start_date_time < :next_day_start
                AND assignment_status IN ('pending', 'confirmed')
                AND completed_at IS NULL
                AND canceled_at IS NULL
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);

        $executed = $stmt->execute([
            ':driver_id' => $driverId,
            ':day_start' => $dayStart,
            ':next_day_start' => $nextDayStart
        ]);

        if (!$executed) {
            throw new \RuntimeException('End of Shift assignment check failed.');
        }

        return $stmt->fetchColumn() !== false;
    }

    public function findLatestCompletedAssignmentByDriver(int $driverId): ?array {
        $sql = "SELECT order_id, start_date_time, completed_at
                FROM work_orders
                WHERE driver_id = :driver_id
                AND assignment_status = 'completed'
                AND completed_at IS NOT NULL
                ORDER BY completed_at DESC, order_id DESC
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);

        $executed = $stmt->execute([
            ':driver_id' => $driverId
        ]);

        if (!$executed) {
            throw new \RuntimeException('Latest completed assignment query failed.');
        }

        $assignment = $stmt->fetch();

        return $assignment !== false ? $assignment : null;
    }

    public function confirmAssignment(int $orderId, int $driverId): bool {
        $sql = "UPDATE work_orders
                SET assignment_status = 'confirmed', confirmed_at = CURRENT_TIMESTAMP
                WHERE order_id = :order_id
                AND driver_id = :driver_id
                AND assignment_status = 'pending'
                AND completed_at IS NULL
                AND canceled_at IS NULL";
        $stmt = $this->pdo->prepare($sql);

        $executed = $stmt->execute([
            ':order_id' => $orderId,
            ':driver_id' => $driverId
        ]);

        if (!$executed) {
            throw new \RuntimeException('Assignment confirmation failed.');
        }

        return $stmt->rowCount() === 1;
    }

    public function cancelAssignment(string $assignmentControl, int $orderId, int $driverId, ?string $reason = null): bool {
        try {
            $this->pdo->beginTransaction();

            $fetchSql = "SELECT order_id, assignment_control, order_ref, driver_id, vehicle_id, assignment_status, completed_at, canceled_at
                    FROM work_orders
                    WHERE assignment_control = :assignment_control
                    AND order_id = :order_id
                    AND driver_id = :driver_id
                    LIMIT 1
                    FOR UPDATE";
            $fetchStmt = $this->pdo->prepare($fetchSql);

            $fetchStmt->execute([
                ':assignment_control' => $assignmentControl,
                ':order_id' => $orderId,
                ':driver_id' => $driverId
            ]);

            $assignment = $fetchStmt->fetch();

            if (!$assignment) {
                throw new \RuntimeException('Assignment could not be found.');
            }

            if (!empty($assignment['completed_at']) || $assignment['assignment_status'] === 'completed') {
                throw new \RuntimeException('A completed assignment cannot be canceled.');
            }

            if (!empty($assignment['canceled_at']) || $assignment['assignment_status'] === 'canceled') {
                throw new \RuntimeException('This assignment has already been canceled.');
            }

            if ($assignment['assignment_status'] !== 'pending') {
                throw new \RuntimeException('Only a pending assignment can be canceled.');
            }

            $previousStatus = $assignment['assignment_status'];

            $updateSql = "UPDATE work_orders
                        SET assignment_status = 'canceled',
                            canceled_at = NOW(),
                            canceled_by = :canceled_by,
                            canceled_by_role = 'driver',
                            cancel_reason = :cancel_reason
                        WHERE assignment_control = :assignment_control
                        AND order_id = :order_id
                        AND driver_id = :driver_id
                        AND assignment_status = 'pending'
                        AND completed_at IS NULL
                        AND canceled_at IS NULL";
            $updateStmt = $this->pdo->prepare($updateSql);

            $updateSaved = $updateStmt->execute([
                ':canceled_by' => $driverId,
                ':cancel_reason' => $reason,
                ':assignment_control' => $assignmentControl,
                ':order_id' => $orderId,
                ':driver_id' => $driverId,
            ]);

            if (!$updateSaved || $updateStmt->rowCount() !== 1) {
                throw new \RuntimeException('Assignment could not be canceled.');
            }

            $historySql = "INSERT INTO assignment_history (order_id, assignment_control, order_ref, driver_id, vehicle_id, action_type, previous_status, new_status, performed_by, performed_by_role, reason)
                        VALUES (:order_id, :assignment_control, :order_ref, :driver_id, :vehicle_id, 'canceled', :previous_status, 'canceled', :performed_by, 'driver', :reason)";
            $historyStmt = $this->pdo->prepare($historySql);

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

            $this->pdo->commit();

            return true;

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }
}

?>