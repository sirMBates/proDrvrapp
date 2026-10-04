<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TimesheetRepository;
use App\Repositories\TimesheetSubmissionRepository;
use Core\Storage;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use RuntimeException;
use Throwable;

class TimesheetFinalizationService {
    public function __construct(private PDO $pdo, private TimesheetSubmissionService $submissionService, private TimesheetSubmissionRepository $submissionRepository, private TimesheetRepository $timesheetRepository, private TimesheetPdfRenderer $pdfRenderer, private Storage $storage) {}

    public function submit(int $driverId, string $periodStart, string $periodEnd, int $weekEndsOn = 6, string $timezone = 'America/New_York'): array {
        /*
         * Revalidate everything at submission time. Never trust data
         * carried forward from the browser review page.
         */
        $document = $this->submissionService->prepareCurrentReview($driverId, $periodStart, $periodEnd, $weekEndsOn, $timezone);
        $companyTimezone = new DateTimeZone($document['timezone']);
        $submittedAt = (new DateTimeImmutable('now', $companyTimezone))->format('Y-m-d H:i:s');

        $storedPdf = null;

        try {
            if (!$this->pdo->beginTransaction()) {
                throw new RuntimeException('The Timesheet submission transaction could not begin.');
            }

            $submissionId = $this->submissionRepository->create([
                'driver_id' => $driverId,
                'period_start' => $document['period_start'],
                'period_end' => $document['period_end'],
                'week_ends_on' => $document['week_ends_on'],
                'timezone' => $document['timezone'],
                'assignment_count' => $document['assignment_count'],
                'total_hours' => $document['period_total_hours'],
                'submitted_at' => $submittedAt,
            ]);

            /*
             * These values become part of the official PDF, but they are
             * sourced from the server-created submission record.
             */
            $document['submission_id'] = $submissionId;
            $document['submitted_at'] = $submittedAt;

            $pdfBytes = $this->pdfRenderer->render($document);
            $storedPdf = $this->storage->saveTimesheetPdf($driverId, $document['period_start'], $document['period_end'], $pdfBytes);

            $this->submissionRepository->updatePdfMetadata($submissionId, $storedPdf['pdf_path'], $storedPdf['pdf_sha256'], $storedPdf['pdf_generated_at']);

            $attachedCount = $this->timesheetRepository->attachLockedEntriesToSubmission($driverId, $submissionId, $document['period_start'], $document['period_end'], $document['assignment_count']);

            if (!$this->pdo->commit()) {
                throw new RuntimeException('The Timesheet submission transaction could not be completed.');
            }

            return [
                'submission_id' => $submissionId,
                'period_start' => $document['period_start'],
                'period_end' => $document['period_end'],
                'assignment_count' => $attachedCount,
                'total_hours' => $document['period_total_hours'],
                'submitted_at' => $submittedAt,
                'pdf_generated_at' => $storedPdf['pdf_generated_at'],
            ];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            if (is_array($storedPdf) && ($storedPdf['created'] ?? false) === true) {
                try {
                    $this->storage->deleteTimesheetPdf((string) $storedPdf['pdf_path'], (string) $storedPdf['pdf_sha256']);
                } catch (Throwable $cleanupError) {
                    throw new RuntimeException('The Timesheet submission failed and its PDF could not be cleaned up.', 0, $e);
                }
            }

            throw $e;
        }
    }
}

?>