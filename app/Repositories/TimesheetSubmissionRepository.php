<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Validation\Validator;
use Core\Database;
use Core\Logger;
use InvalidArgumentException;
use PDO;
use PDOException;

class TimesheetSubmissionRepository {
    private PDO $pdo;
    private ?Logger $logger;

    public function __construct(?PDO $pdo = null, ?Logger $logger = null) {
        $this->pdo = $pdo ?? (new Database())->connect();
        $this->logger = $logger;
    }

    public function findForDriverPeriod(int $driverId, string $periodStart, string $periodEnd): ?array {
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

        $sql = "SELECT submission_id, driver_id, period_start, period_end, week_ends_on, timezone, assignment_count, total_hours, pdf_path, pdf_sha256, pdf_generated_at, submitted_at, created_at, updated_at
                FROM timesheet_submissions
                WHERE driver_id = :driver_id
                AND period_start = :period_start
                AND period_end = :period_end
                LIMIT 1";

        try {
            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ':driver_id' => $driverId,
                ':period_start' => $periodStart,
                ':period_end' => $periodEnd,
            ]);

            $submission = $stmt->fetch();
            return $submission !== false ? $submission : null;
        } catch (PDOException $e) {
            $this->logger?->error('[TIMESHEET SUBMISSION] Failed loading driver period: ' . $e->getMessage());
            throw $e;
        }
    }
}

?>