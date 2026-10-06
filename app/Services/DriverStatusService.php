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

        if ($driverStatus === DriverStatus::END_OF_SHIFT) {
            return $this->endShiftIfNeeded($driverId);
        }

        $statusId = $this->driverStatusRepository->createStatus($driverId, $driverStatus->value, null);
        if ($statusId < 1) {
            throw new RuntimeException('Driver status could not be created.');
        }

        $statusRecord = $this->driverStatusRepository->findLatestByDriverId($driverId);
        if ($statusRecord === null) {
            throw new RuntimeException('Driver status could not be retrieved.');
        }

        return $this->normalizeStatusRecord($statusRecord);
    }

    public function endShiftIfNeeded(int $driverId): array {
        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        if ($this->emergencyService->hasActiveEmergency($driverId)) {
            throw new InvalidArgumentException('End of Shift is unavailable while an emergency is active.');
        }

        $operationalDate = $this->resolveEOSOperationalDate($driverId);

        $latestStatus = $this->driverStatusRepository->findLatestByDriverId($driverId);
        if ($latestStatus !== null && ($latestStatus['status'] ?? '') === DriverStatus::END_OF_SHIFT->value && ($latestStatus['operational_date'] ?? null) === $operationalDate) {
            return $this->normalizeStatusRecord($latestStatus);
        }

        $statusId = $this->driverStatusRepository->createStatus($driverId, DriverStatus::END_OF_SHIFT->value, $operationalDate);
        if ($statusId < 1) {
            throw new RuntimeException('End of Shift status could not be created.');
        }

        $statusRecord = $this->driverStatusRepository->findLatestByDriverId($driverId);
        if ($statusRecord === null || ($statusRecord['status'] ?? '') !== DriverStatus::END_OF_SHIFT->value) {
            throw new RuntimeException('End of Shift status could not be retrieved.');
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

        if (($statusRecord['status'] ?? '') === DriverStatus::END_OF_SHIFT->value) {
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
            'operationalDate' => $statusRecord['operational_date'] ?? null,
            'statusTimestamp' => (string) $statusRecord['status_timestamp']
        ];
    }

    private function resolveEOSOperationalDate(int $driverId): string {
        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        $currentDateTime = new \DateTimeImmutable('now');
        $operationalAssignment = $this->assignmentRepository->findOperationalAssignmentForEOS($driverId, $currentDateTime->format('Y-m-d H:i:s'));

        if ($operationalAssignment === null) {
            throw new InvalidArgumentException('End of Shift requires an operational assignment.');
        }

        $startDateTime = new \DateTimeImmutable((string) $operationalAssignment['start_date_time']);
        $operationalDayStart = $startDateTime->setTime(0, 0, 0);
        $nextOperationalDayStart = $operationalDayStart->modify('+1 day');

        $hasBlockingAssignments = $this->assignmentRepository->hasBlockingAssignmentsForEOS($driverId, $operationalDayStart->format('Y-m-d H:i:s'), $nextOperationalDayStart->format('Y-m-d H:i:s'));

        if ($hasBlockingAssignments) {
            throw new InvalidArgumentException('End of Shift is not available while assignments remain incomplete.');
        }

        return $operationalDayStart->format('Y-m-d');
    }
}

?>