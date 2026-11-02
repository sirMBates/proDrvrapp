<?php

declare(strict_types=1);

namespace App\Repositories;

use Core\Database;
use PDO;

class AccessKeyCycleRepository {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? (new Database())->connect();
    }

    public function create(string $startsAt, string $expiresAt, ?int $createdBy = null): int {
        $sql = "INSERT INTO access_key_cycles (starts_at, expires_at, created_by)
                VALUES (:starts_at, :expires_at, :created_by)";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':starts_at' => $startsAt,
            ':expires_at' => $expiresAt,
            ':created_by' => $createdBy
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $cycleId): ?array {
        $sql = "SELECT * FROM access_key_cycles
                WHERE cycle_id = :cycle_id
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':cycle_id' => $cycleId
        ]);

        $cycle = $stmt->fetch();

        return $cycle !== false ? $cycle : null;
    }

    public function findCurrent(): ?array {
        $sql = "SELECT * FROM access_key_cycles
                WHERE starts_at <= NOW()
                AND expires_at >= NOW()
                ORDER BY starts_at DESC, cycle_id DESC
                LIMIT 1";
        $stmt = $this->pdo->query($sql);

        $cycle = $stmt->fetch();

        return $cycle !== false ? $cycle : null;
    }

    public function findLatest(): ?array {
        $sql = "SELECT * FROM access_key_cycles
                ORDER BY cycle_id DESC
                LIMIT 1";
        $stmt = $this->pdo->query($sql);

        $cycle = $stmt->fetch();

        return $cycle !== false ? $cycle : null;
    }
}

?>