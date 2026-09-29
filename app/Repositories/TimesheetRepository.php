<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use PDOException;
use RuntimeException;
use Core\Logger;
use Core\Database;
use App\Validation\Validator;
use InvalidArgumentException;

class TimesheetRepository {
    private PDO $pdo;
    private ?Logger $logger;

    public function __construct(?PDO $pdo = null, ?Logger $logger = null) {
        $this->pdo = $pdo ?? (new Database())->connect();
        $this->logger = $logger;
    }

    public function createCompletionSnapshot(array $assignment): bool {
        $requiredFields = [
            'assignment_control',
            'order_id',
            'driver_id',
            'vehicle_id',
            'start_date_time',
            'actual_drop_time',
            'actual_end_time',
            'total_job_time',
            'completed_at',
        ];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $assignment) || $assignment[$field] === null || trim((string) $assignment[$field]) === '') {
                throw new RuntimeException("Missing required Timesheet snapshot field: {$field}");
            }
        }

        $assignmentDate = $this->extractAssignmentDate((string) $assignment['start_date_time']);

        $jobDetails = $this->buildJobDetails($assignment['pickup_details'] ?? null, $assignment['destination_details'] ?? null);

        $sql = "INSERT INTO timesheet_entries (
                assignment_control, order_id, driver_id, origin, destination, vehicle_id, assignment_date, spot_time, actual_drop_time, actual_end_time, total_job_time, job_details, completed_at) 
                VALUES (:assignment_control, :order_id, :driver_id, :origin, :destination, :vehicle_id, :assignment_date, :spot_time, :actual_drop_time, :actual_end_time, :total_job_time, :job_details, :completed_at)";
        try {
            $stmt = $this->pdo->prepare($sql);

            $success = $stmt->execute([
                ':assignment_control' => (string) $assignment['assignment_control'],
                ':order_id' => (int) $assignment['order_id'],
                ':driver_id' => (int) $assignment['driver_id'],
                ':origin' => $this->nullableString($assignment['origin'] ?? null),
                ':destination' => $this->nullableString($assignment['destination'] ?? null),
                ':vehicle_id' => (string) $assignment['vehicle_id'],
                ':assignment_date' => $assignmentDate,
                ':spot_time' => $this->nullableString($assignment['spot_time'] ?? null),
                ':actual_drop_time' => (string) $assignment['actual_drop_time'],
                ':actual_end_time' => (string) $assignment['actual_end_time'],
                ':total_job_time' => $assignment['total_job_time'],
                ':job_details' => $jobDetails,
                ':completed_at' => (string) $assignment['completed_at'],
            ]);

            if (!$success || $stmt->rowCount() !== 1) {
                throw new RuntimeException('Timesheet completion snapshot was not created.');
            }

            $this->logger?->info('[TIMESHEET] Completion snapshot created for assignment_control ' . $assignment['assignment_control']);

            return true;
        } catch (PDOException $e) {
            $this->logger?->error('[TIMESHEET] Failed creating completion snapshot: ' . $e->getMessage());

            throw $e;
        }
    }

    private function extractAssignmentDate(string $startDateTime): string {
        $timestamp = strtotime($startDateTime);

        if ($timestamp === false) {
            throw new RuntimeException('Invalid start_date_time for Timesheet snapshot.');
        }

        return date('Y-m-d', $timestamp);
    }

    private function buildJobDetails(mixed $pickupDetails, mixed $destinationDetails): ?string {
        $pickup = trim((string) ($pickupDetails ?? ''));
        $destination = trim((string) ($destinationDetails ?? ''));

        $parts = [];

        if ($pickup !== '') {
            $parts[] = "Pickup:\n{$pickup}";
        }

        if ($destination !== '') {
            $parts[] = "Destination:\n{$destination}";
        }

        return $parts === [] ? null : implode("\n\n", $parts);
    }

    private function nullableString(mixed $value): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    public function findForDriverPeriod(int $driverId, string $periodStart, string $periodEnd): array {
        $periodStart = trim($periodStart);
        $periodEnd = trim($periodEnd);

        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        if (!Validator::date($periodStart)) {
            throw new InvalidArgumentException('Invalid Timesheet period start date.');
        }

        if (!Validator::date($periodEnd)) {
            throw new InvalidArgumentException('Invalid Timesheet period end date.');
        }

        if ($periodStart > $periodEnd) {
            throw new InvalidArgumentException('The Timesheet period start cannot be after its end.');
        }

        $sql = "SELECT timesheet_id, assignment_control, order_id, driver_id, origin, destination, vehicle_id, assignment_date, spot_time, actual_drop_time, actual_end_time, total_job_time, SUM(total_job_time) OVER (PARTITION BY driver_id, assignment_date) AS total_shift_hours, SUM(total_job_time) OVER () AS period_total_hours, job_details, tolls_used, tip, job_pay, locked_at, completed_at, created_at, updated_at
                FROM timesheet_entries
                WHERE driver_id = :driver_id
                AND assignment_date >= :period_start
                AND assignment_date <= :period_end
                ORDER BY assignment_date ASC, actual_end_time ASC, timesheet_id ASC";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':driver_id' => $driverId,
                ':period_start' => $periodStart,
                ':period_end' => $periodEnd,
            ]);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logger?->error('[TIMESHEET] Failed loading driver period: ' . $e->getMessage());
            throw $e;
        }
    }

    public function saveAndLockDriverEntries(int $driverId, array $entries): int {
        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        if ($entries === []) {
            throw new InvalidArgumentException('No Timesheet entries were provided.');
        }

        $selectSql = "SELECT tolls_used, tip, locked_at
                    FROM timesheet_entries
                    WHERE timesheet_id = :timesheet_id
                    AND driver_id = :driver_id
                    FOR UPDATE";

        $updateSql = "UPDATE timesheet_entries
                    SET tolls_used = :tolls_used,
                    tip = :tip,
                    locked_at = CURRENT_TIMESTAMP,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE timesheet_id = :timesheet_id
                    AND driver_id = :driver_id
                    AND locked_at IS NULL";

        $transactionStarted = false;

        try {
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
                $transactionStarted = true;
            }

            $selectStmt = $this->pdo->prepare($selectSql);
            $updateStmt = $this->pdo->prepare($updateSql);

            $lockedCount = 0;

            foreach ($entries as $entry) {
                $timesheetId = (int) $entry['timesheet_id'];
                $tollsUsed = $entry['tolls_used'];
                $tip = $entry['tip'];

                $selectStmt->execute([
                    ':timesheet_id' => $timesheetId,
                    ':driver_id' => $driverId,
                ]);

                $storedEntry = $selectStmt->fetch();

                if ($storedEntry === false) {
                    throw new RuntimeException('A Timesheet entry could not be found for this driver.');
                }

                /*
                * Make retries idempotent. If the original request succeeded but
                * its response was interrupted, repeating the same values should
                * not fail or modify the locked entry.
                */
                if ($storedEntry['locked_at'] !== null) {
                    $storedTolls = $storedEntry['tolls_used'] === null ? null : (int) $storedEntry['tolls_used'];
                    $storedTip = $storedEntry['tip'] === null ? null : (int) $storedEntry['tip'];

                    if ($storedTolls !== $tollsUsed || $storedTip !== $tip) {
                        throw new RuntimeException('A locked Timesheet entry cannot be changed.');
                    }

                    continue;
                }

                $updateStmt->execute([
                    ':tolls_used' => $tollsUsed,
                    ':tip' => $tip,
                    ':timesheet_id' => $timesheetId,
                    ':driver_id' => $driverId,
                ]);

                if ($updateStmt->rowCount() !== 1) {
                    throw new RuntimeException('A Timesheet entry could not be saved and locked.');
                }

                $lockedCount++;
            }

            if ($transactionStarted) {
                $this->pdo->commit();
            }

            $this->logger?->info("[TIMESHEET] Saved and locked {$lockedCount} entries for driver {$driverId}.");
            return $lockedCount;
        } catch (\Throwable $e) {
            if ($transactionStarted && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $this->logger?->error('[TIMESHEET] Save and lock failed: ' . $e->getMessage());
            throw $e;
        }
    }
}

?>