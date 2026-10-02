<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AssignmentRepository;
use App\Repositories\TimesheetSubmissionRepository;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use RuntimeException;

class TimesheetSubmissionService {
    public function __construct(private TimesheetSubmissionRepository $submissionRepository, private AssignmentRepository $assignmentRepository, private TimesheetService $timesheetService, private PayPeriodService $payPeriodService) {}

    public function prepareCurrentReview(int $driverId, string $requestedPeriodStart, string $requestedPeriodEnd, int $weekEndsOn = 6, string $timezone = 'America/New_York', ?DateTimeImmutable $now = null): array {
        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        $requestedPeriodStart = trim($requestedPeriodStart);
        $requestedPeriodEnd = trim($requestedPeriodEnd);

        $period = $this->payPeriodService->getCurrentPeriod($weekEndsOn, $timezone, $now);

        /*
         * Do not trust hidden form dates. They must match the
         * authoritative pay period calculated by the server.
         */
        if ($requestedPeriodStart !== $period['period_start'] || $requestedPeriodEnd !== $period['period_end']) {
            throw new InvalidArgumentException('The requested Timesheet period is invalid.');
        }

        if (!$period['submission_available']) {
            throw new RuntimeException('This Timesheet is not yet available for review and submission.');
        }

        $existingSubmission = $this->submissionRepository->findForDriverPeriod($driverId, $period['period_start'], $period['period_end']);
        if ($existingSubmission !== null) {
            throw new RuntimeException('This Timesheet has already been submitted.');
        }

        $timesheet = $this->timesheetService->getDriverPeriod($driverId, $period['period_start'], $period['period_end']);
        if ($timesheet['assignment_count'] < 1) {
            throw new RuntimeException('This Timesheet has no completed assignments to submit.');
        }

        foreach ($timesheet['days'] as $day) {
            foreach ($day['entries'] as $entry) {
                if (!$entry['locked']) {
                    throw new RuntimeException('Every Timesheet entry must be saved and locked before review.');
                }

                if ($entry['submission_id'] !== null) {
                    throw new RuntimeException('A Timesheet entry has already been submitted.');
                }
            }
        }

        $companyTimezone = new DateTimeZone($period['timezone']);
        $periodStart = new DateTimeImmutable($period['period_start'] . ' 00:00:00', $companyTimezone);
        $nextPeriodStart = new DateTimeImmutable($period['period_end'] . ' 00:00:00', $companyTimezone);
        $nextPeriodStart = $nextPeriodStart->modify('+1 day');

        $hasBlockingAssignments = $this->assignmentRepository->hasBlockingAssignmentsForTimesheet($driverId, $periodStart->format('Y-m-d H:i:s'), $nextPeriodStart->format('Y-m-d H:i:s'));

        if ($hasBlockingAssignments) {
            throw new RuntimeException('This Timesheet cannot be submitted while assignments in the pay period remain incomplete.');
        }

        return [
            'period_start' => $period['period_start'],
            'period_end' => $period['period_end'],
            'week_ends_on' => $period['week_ends_on'],
            'timezone' => $period['timezone'],
            'submission_opens_at' => $period['submission_opens_at'],
            'submission_available' => true,
            'assignment_count' => $timesheet['assignment_count'],
            'period_total_hours' => $timesheet['period_total_hours'],
            'days' => $timesheet['days'],
        ];
    }
}

?>