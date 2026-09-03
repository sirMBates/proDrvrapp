<?php

declare(strict_types=1);

namespace App\Repositories;

use Core\Database;
use PDO;
use RuntimeException;

class EmergencyRepository {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? (new Database())->connect();
    }

    public function findActiveByDriverId(int $driverId): ?array {
        $sql = "SELECT emergency_id, driver_id, triggered_at, acknowledged_at, acknowledged_by, resolved_at, resolved_by, resolution_reason, resolution_notes
                FROM emergency_incidents
                WHERE driver_id = :driver_id AND resolved_at IS NULL
                ORDER BY triggered_at DESC, emergency_id DESC
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);

        $executed = $stmt->execute([
            ':driver_id' => $driverId
        ]);

        if (!$executed) {
            throw new RuntimeException('Active emergency lookup failed.');
        }

        $emergency = $stmt->fetch();

        return $emergency !== false ? $emergency : null;
    }

    public function createEmergency(int $driverId): int {
        $sql = "INSERT INTO emergency_incidents (driver_id)
                VALUES (:driver_id)";

        $stmt = $this->pdo->prepare($sql);

        $executed = $stmt->execute([
            ':driver_id' => $driverId
        ]);

        if (!$executed) {
            throw new RuntimeException('Emergency incident could not be created.');
        }

        return (int) $this->pdo->lastInsertId();
    }
}

?>