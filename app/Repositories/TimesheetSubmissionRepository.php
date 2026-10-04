<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Validation\Validator;
use Core\Database;
use Core\Logger;
use InvalidArgumentException;
use PDO;
use PDOException;
use RuntimeException;

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

    public function create(array $submission): int {
        $driverId = (int) ($submission['driver_id'] ?? 0);
        $periodStart = trim((string) ($submission['period_start'] ?? ''));
        $periodEnd = trim((string) ($submission['period_end'] ?? ''));
        $weekEndsOn = (int) ($submission['week_ends_on'] ?? 0);
        $timezone = trim((string) ($submission['timezone'] ?? ''));
        $assignmentCount = (int) ($submission['assignment_count'] ?? 0);
        $totalHours = trim((string) ($submission['total_hours'] ?? ''));
        $submittedAt = trim((string) ($submission['submitted_at'] ?? ''));

        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid Timesheet submission driver ID.');
        }

        if (!Validator::date($periodStart) || !Validator::date($periodEnd) || $periodStart > $periodEnd) {
            throw new InvalidArgumentException('Invalid Timesheet submission period.');
        }

        if ($weekEndsOn < 1 || $weekEndsOn > 7) {
            throw new InvalidArgumentException('Invalid Timesheet week-ending day.');
        }

        if ($timezone === '' || $assignmentCount < 1 || !Validator::nonNegativeDecimal($totalHours) || !Validator::dateTime($submittedAt)) {
            throw new InvalidArgumentException('The Timesheet submission data is incomplete.');
        }

        $sql = "INSERT INTO timesheet_submissions (driver_id, period_start, period_end, week_ends_on, timezone, assignment_count, total_hours, submitted_at) 
                VALUES (:driver_id, :period_start, :period_end, :week_ends_on, :timezone, :assignment_count, :total_hours, :submitted_at)";

        try {
            $stmt = $this->pdo->prepare($sql);

            $success = $stmt->execute([
                ':driver_id' => $driverId,
                ':period_start' => $periodStart,
                ':period_end' => $periodEnd,
                ':week_ends_on' => $weekEndsOn,
                ':timezone' => $timezone,
                ':assignment_count' => $assignmentCount,
                ':total_hours' => $totalHours,
                ':submitted_at' => $submittedAt,
            ]);

            $submissionId = (int) $this->pdo->lastInsertId();
            if (!$success || $submissionId < 1) {
                throw new RuntimeException('The Timesheet submission could not be created.');
            }

            return $submissionId;
        } catch (PDOException $e) {
            $this->logger?->error('[TIMESHEET SUBMISSION] Failed creating submission: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updatePdfMetadata(int $submissionId, string $pdfPath, string $pdfSha256, string $pdfGeneratedAt): bool {
        $pdfPath = trim($pdfPath);
        $pdfSha256 = strtolower(trim($pdfSha256));
        $pdfGeneratedAt = trim($pdfGeneratedAt);

        if ($submissionId < 1) {
            throw new InvalidArgumentException('Invalid Timesheet submission ID.');
        }

        if ($pdfPath === '') {
            throw new InvalidArgumentException('The Timesheet PDF path is required.');
        }

        if (!preg_match('/^[a-f0-9]{64}$/', $pdfSha256)) {
            throw new InvalidArgumentException('The Timesheet PDF hash is invalid.');
        }

        if (!Validator::dateTime($pdfGeneratedAt)) {
            throw new InvalidArgumentException('The Timesheet PDF generation timestamp is invalid.');
        }

        $sql = "UPDATE timesheet_submissions
                SET pdf_path = :pdf_path,
                    pdf_sha256 = :pdf_sha256,
                    pdf_generated_at = :pdf_generated_at,
                    updated_at = CURRENT_TIMESTAMP
                WHERE submission_id = :submission_id
                AND pdf_path IS NULL";

        try {
            $stmt = $this->pdo->prepare($sql);

            $success = $stmt->execute([
                ':pdf_path' => $pdfPath,
                ':pdf_sha256' => $pdfSha256,
                ':pdf_generated_at' => $pdfGeneratedAt,
                ':submission_id' => $submissionId,
            ]);

            if (!$success || $stmt->rowCount() !== 1) {
                throw new RuntimeException('The Timesheet PDF metadata could not be saved.');
            }

            return true;
        } catch (PDOException $e) {
            $this->logger?->error('[TIMESHEET SUBMISSION] Failed saving PDF metadata: ' . $e->getMessage());
            throw $e;
        }
    }
}

?>