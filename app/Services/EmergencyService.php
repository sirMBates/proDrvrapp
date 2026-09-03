<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DriverStatus;
use App\Repositories\DriverStatusRepository;
use App\Repositories\EmergencyRepository;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

class EmergencyService {
    public function __construct(private PDO $pdo, private EmergencyRepository $emergencyRepository, private DriverStatusRepository $driverStatusRepository) {}

    public function activateEmergency(int $driverId): array {
        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        $activeEmergency = $this->emergencyRepository->findActiveByDriverId($driverId);
        if ($activeEmergency !== null) {
            throw new InvalidArgumentException('An emergency is already active.');
        }

        try {
            $this->pdo->beginTransaction();
            $emergencyId = $this->emergencyRepository->createEmergency($driverId);
            if ($emergencyId < 1) {
                throw new RuntimeException('Emergency incident could not be created.');
            }

            $emergency = $this->emergencyRepository->findActiveByDriverId($driverId);
            if ($emergency === null) {
                throw new RuntimeException('Emergency incident could not be retrieved.');
            }

            $statusId = $this->driverStatusRepository->createStatus($driverId, DriverStatus::EMERGENCY->value);
            if ($statusId < 1) {
                throw new RuntimeException('Emergency status could not be created.');
            }

            $emergency = $this->emergencyRepository->findActiveByDriverId($driverId);
            if ($emergency === null) {
                throw new RuntimeException('Emergency incident could not be retrieved.');
            }

            $this->pdo->commit();

            return $this->normalizeEmergency($emergency);
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollback();
            }
            throw $e;
        }
    }

    private function normalizeEmergency(array $emergency): array {
        return [
            'emergencyId' => (int) $emergency['emergency_id'],
            'driverId' => (int) $emergency['driver_id'],
            'triggeredAt' => $emergency['triggered_at'],
            'acknowledgedAt' => $emergency['acknowledged_at'],
            'acknowledgedBy' => $emergency['acknowledged_by'] !== null ? (int) $emergency['acknowledged_by'] : null,
            'resolvedAt' => $emergency['resolved_at'],
            'resolvedBy' => $emergency['resolved_by'] !== null ? (int) $emergency['resolved_by'] : null,
            'resolutionReason' => $emergency['resolution_reason'],
            'resolutionNotes' => $emergency['resolution_notes']
        ];
    }
}


?>