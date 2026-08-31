<?php

declare(strict_types=1);

namespace App\Repositories;

use Core\Database;
use PDO;

class DriverStatusRepository {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? (new Database())->connect();
    }

    public function createStatus(int $driverId, string $status): int {
        $sql = "INSERT INTO driver_status (driver_id, status)
                VALUES (:driver_id, :status)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':driver_id' => $driverId,
            ':status' => $status
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function findLatestByDriverId(int $driverId): ?array {
        $sql = "SELECT status_id, driver_id, status, status_timestamp
                FROM driver_status
                WHERE driver_id = :driver_id
                ORDER BY status_timestamp DESC, status_id DESC
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':driver_id' => $driverId
        ]);

        $driverStatus = $stmt->fetch();

        return $driverStatus !== false ? $driverStatus : null;
    }

    public function findRecentByDriverId(int $driverId, int $limit = 20): array {
        $sql = "SELECT status_id, driver_id, status, status_timestamp
                FROM driver_status
                WHERE driver_id = :driver_id
                ORDER BY status_timestamp DESC, status_id DESC
                LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':driver_id', $driverId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}

?>