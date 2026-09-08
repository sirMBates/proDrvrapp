<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use PDOException;
use RuntimeException;
use Core\Logger;
use Core\Database;

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
                assignment_control, order_id, driver_id, origin, destination, vehicle_id, assignment_date, spot_time actual_drop_time, actual_end_time, total_job_time, job_details, completed_at) 
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
}

?>