<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TimesheetRepository;
use App\Repositories\DriverStatusRepository;
use App\Validation\Validator;
use InvalidArgumentException;

class TimesheetService {
    public function __construct(private TimesheetRepository $timesheetRepository, private DriverStatusRepository $driverStatusRepository, private PayPeriodService $payPeriodService) {}

    public function getCurrentDriverPeriod(int $driverId, int $weekEndsOn = 6, string $timezone = 'America/New_York'): array {
        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        $period = $this->payPeriodService->getCurrentPeriod($weekEndsOn, $timezone);
        $timesheet = $this->getDriverPeriod($driverId, $period['period_start'], $period['period_end']);

        return [
            'period_start' => $timesheet['period_start'],
            'period_end' => $timesheet['period_end'],
            'week_ends_on' => $period['week_ends_on'],
            'timezone' => $period['timezone'],
            'submission_opens_at' => $period['submission_opens_at'],
            'submission_available' => $period['submission_available'],
            'assignment_count' => $timesheet['assignment_count'],
            'period_total_hours' => $timesheet['period_total_hours'],
            'days' => $timesheet['days']
        ];
    }

    public function getDriverPeriod(int $driverId, string $periodStart, string $periodEnd): array {
        $periodStart = trim($periodStart);
        $periodEnd = trim($periodEnd);

        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        $entries = $this->timesheetRepository->findForDriverPeriod($driverId, $periodStart, $periodEnd);
        $endOfShiftRecords = $this->driverStatusRepository->findEndOfShiftForPeriod($driverId, $periodStart, $periodEnd);

        $endOfDutyByDate = [];

        foreach ($endOfShiftRecords as $statusRecord) {
            $operationalDate = $statusRecord['operational_date'];

            if ($operationalDate === null) {
                continue;
            }

            $endOfDutyByDate[(string) $operationalDate] = (string) $statusRecord['status_timestamp'];
        }

        $days = [];

        foreach ($entries as $entry) {
            $assignmentDate = (string) $entry['assignment_date'];

            if (!isset($days[$assignmentDate])) {
                $days[$assignmentDate] = [
                    'assignment_date' => $assignmentDate,
                    'end_of_duty' => $endOfDutyByDate[$assignmentDate] ?? null,
                    'total_shift_hours' => (string) $entry['total_shift_hours'],
                    'entries' => []
                ];
            }

            $days[$assignmentDate]['entries'][] = $this->formatEntry($entry);
        }

        $periodTotalHours = $entries === [] ? '0.00' : (string) $entries[0]['period_total_hours'];

        return [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'assignment_count' => count($entries),
            'period_total_hours' => $periodTotalHours,
            'days' => array_values($days)
        ];
    }

    public function saveAndLockDriverEntries(int $driverId, array $entries): array {
        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        if ($entries === []) {
            throw new InvalidArgumentException('No Timesheet entries were provided.');
        }

        $normalizedEntries = [];
        $seenTimesheetIds = [];

        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                throw new InvalidArgumentException('Invalid Timesheet entry data.');
            }

            $timesheetId = $entry['timesheet_id'] ?? null;
            if (!Validator::positiveInteger($timesheetId)) {
                throw new InvalidArgumentException('Invalid Timesheet entry ID.');
            }

            $timesheetId = (int) $timesheetId;
            if (isset($seenTimesheetIds[$timesheetId])) {
                throw new InvalidArgumentException('Duplicate Timesheet entry IDs are not allowed.');
            }

            if (!array_key_exists('tolls_used', $entry) || !array_key_exists('tip', $entry)) {
                throw new InvalidArgumentException('Tolls and tip values must be included.');
            }

            $normalizedEntries[] = [
                'timesheet_id' => $timesheetId,
                'tolls_used' => $this->normalizeOptionalYesNo($entry['tolls_used'], 'tolls'),
                'tip' => $this->normalizeOptionalYesNo($entry['tip'], 'tip')
            ];

            $seenTimesheetIds[$timesheetId] = true;
        }

        $lockedCount = $this->timesheetRepository->saveAndLockDriverEntries($driverId, $normalizedEntries);

        return [
            'entry_count' => count($normalizedEntries),
            'locked_count' => $lockedCount
        ];
    }

    private function formatEntry(array $entry): array {
        return [
            'timesheet_id' => (int) $entry['timesheet_id'],
            'assignment_control' => (string) $entry['assignment_control'],
            'order_id' => (int) $entry['order_id'],
            'origin' => $entry['origin'],
            'destination' => $entry['destination'],
            'vehicle_id' => (string) $entry['vehicle_id'],
            'assignment_date' => (string) $entry['assignment_date'],
            'spot_time' => $entry['spot_time'],
            'actual_drop_time' => $entry['actual_drop_time'],
            'actual_end_time' => $entry['actual_end_time'],
            'total_job_time' => (string) $entry['total_job_time'],
            'job_details' => $entry['job_details'],
            'tolls_used' => $this->nullableBoolean($entry['tolls_used']),
            'tip' => $this->nullableBoolean($entry['tip']),
            'job_pay' => $entry['job_pay'] === null ? null : (string) $entry['job_pay'],
            'locked' => $entry['locked_at'] !== null,
            'locked_at' => $entry['locked_at'],
            'completed_at' => $entry['completed_at']
        ];
    }

    private function nullableBoolean(mixed $value): ?bool {
        if ($value === null) {
            return null;
        }

        return (int) $value === 1;
    }

    private function normalizeOptionalYesNo(mixed $value, string $field): ?int {
        if ($value === null) {
            return null;
        }

        if ($value === true || $value === 1 || $value === '1') {
            return 1;
        }

        if ($value === false || $value === 0 || $value === '0') {
            return 0;
        }

        throw new InvalidArgumentException("Invalid {$field} selection.");
    }
}