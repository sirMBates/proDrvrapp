<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TimesheetSubmissionRepository;
use InvalidArgumentException;
use RuntimeException;

class TimesheetHistoryService {
    public function __construct(private TimesheetSubmissionRepository $submissionRepository) {}

    public function getDriverHistory(int $driverId, int $limit = 25): array {
        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        $submissions = $this->submissionRepository->findRecentByDriverId($driverId, $limit);
        return array_map(fn(array $submission): array => $this->normalizeHistoryRecord($submission), $submissions);
    }

    public function getDriverSubmission(int $submissionId, int $driverId): array {
        if ($submissionId < 1) {
            throw new InvalidArgumentException('Invalid Timesheet submission ID.');
        }

        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        $submission = $this->submissionRepository->findByIdAndDriverId($submissionId, $driverId);
        if ($submission === null) {
            throw new RuntimeException('The requested Timesheet submission was not found.');
        }

        return $submission;
    }

    private function normalizeHistoryRecord(array $submission): array {
        $pdfPath = trim((string) ($submission['pdf_path'] ?? ''));
        $pdfHash = trim((string) ($submission['pdf_sha256'] ?? ''));

        return [
            'submission_id' => (int) $submission['submission_id'],
            'period_start' => (string) $submission['period_start'],
            'period_end' => (string) $submission['period_end'],
            'week_ends_on' => (int) $submission['week_ends_on'],
            'timezone' => (string) $submission['timezone'],
            'assignment_count' => (int) $submission['assignment_count'],
            'total_hours' => (string) $submission['total_hours'],
            'pdf_available' => $pdfPath !== '' && preg_match('/^[a-f0-9]{64}$/', strtolower($pdfHash)) === 1,
            'pdf_generated_at' => $submission['pdf_generated_at'],
            'submitted_at' => (string) $submission['submitted_at']
        ];
    }
}

?>