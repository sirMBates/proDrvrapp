<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DriverStatus;
use App\Repositories\DriverStatusRepository;
use App\Repositories\AssignmentRepository;
use App\Services\EmergencyService;
use InvalidArgumentException;
use RuntimeException;

class DriverStatusService {
    public function __construct(private DriverStatusRepository $driverStatusRepository, private AssignmentRepository $assignmentRepository, private EmergencyService $emergencyService) {}

    public function changeStatus(int $driverId, string $status): array {
        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        $driverStatus = DriverStatus::tryFrom($status);
        if ($driverStatus === null) {
            throw new InvalidArgumentException('Invalid driver status.');
        }

        if ($driverStatus === DriverStatus::EMERGENCY) {
            $this->emergencyService->activateEmergency($driverId);

            $statusRecord = $this->driverStatusRepository->findLatestByDriverId($driverId);
            if ($statusRecord === null) {
                throw new RuntimeException('Emergency status could not be retrieved.');
            }

            return $this->normalizeStatusRecord($statusRecord);
        }

        if ($this->emergencyService->hasActiveEmergency($driverId)) {
            throw new InvalidArgumentException('Status changes are unavailable while an emergency is active.');
        }

        if ($driverStatus === DriverStatus::END_OF_SHIFT && !$this->isEOSReady($driverId)) {
            throw new InvalidArgumentException('End of Shift is not available while assignments remain incomplete.');
        }

        $statusId = $this->driverStatusRepository->createStatus($driverId, $driverStatus->value);
        if ($statusId < 1) {
            throw new RuntimeException('Driver status could not be created.');
        }

        $statusRecord = $this->driverStatusRepository->findLatestByDriverId($driverId);
        if ($statusRecord === null) {
            throw new RuntimeException('Driver status could not be retrieved.');
        }

        return $this->normalizeStatusRecord($statusRecord);
    }

    public function getCurrentStatus(int $driverId): ?array {
        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        $statusRecord = $this->driverStatusRepository->findLatestByDriverId($driverId);
        if ($statusRecord === null) {
            return null;
        }

        return $this->normalizeStatusRecord($statusRecord);
    }

    public function getRecentHistory(int $driverId, int $limit = 20): array {
        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        if ($limit < 1 || $limit > 100) {
            throw new InvalidArgumentException('Invalid status history limit.');
        }

        $history = $this->driverStatusRepository->findRecentByDriverId($driverId, $limit);

        return array_map(fn(array $statusRecord): array => $this->normalizeStatusRecord($statusRecord), $history);
    }

    private function normalizeStatusRecord(array $statusRecord): array {
        return [
            'statusId' => (int) $statusRecord['status_id'],
            'driverId' => (int) $statusRecord['driver_id'],
            'driverStatus' => (string) $statusRecord['status'],
            'statusTimestamp' => (string) $statusRecord['status_timestamp']
        ];
    }

    private function isEOSReady(int $driverId): bool {
        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        $latestCompletedAssignment = $this->assignmentRepository->findLatestCompletedAssignmentByDriver($driverId);
        if ($latestCompletedAssignment === null) {
            return false;
        }

        $startDateTime = new \DateTimeImmutable($latestCompletedAssignment['start_date_time']);
        $dayStart = $startDateTime->setTime(0, 0, 0);
        $nextDayStart = $dayStart->modify('+1 day');

        return !$this->assignmentRepository->hasBlockingAssignmentsForEOS($driverId, $dayStart->format('Y-m-d H:i:s'), $nextDayStart->format('Y-m-d H:i:s'));
    }
}

?>