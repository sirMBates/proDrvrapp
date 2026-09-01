<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DriverStatus;
use App\Repositories\DriverStatusRepository;
use InvalidArgumentException;
use RuntimeException;

class DriverStatusService {
    public function __construct(private DriverStatusRepository $driverStatusRepository) {}

    public function changeStatus(int $driverId, string $status): array {
        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        $driverStatus = DriverStatus::tryFrom($status);
        if ($driverStatus === null) {
            throw new InvalidArgumentException('Invalid driver status.');
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
}

?>