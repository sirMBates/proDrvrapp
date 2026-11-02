<?php

declare(strict_types=1);

namespace App\Repositories;

use Core\Database;
use PDO;

class AccessKeyRepository {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? (new Database())->connect();
    }

    public function create(int $cycleId, string $role, string $keyHash, string $encryptedKey, ?int $createdBy = null): int {
        $sql = "INSERT INTO access_keys (cycle_id, role, key_hash, encrypted_key, created_by)
                VALUES (:cycle_id, :role, :key_hash, :encrypted_key, :created_by)";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':cycle_id' => $cycleId,
            ':role' => $role,
            ':key_hash' => $keyHash,
            ':encrypted_key' => $encryptedKey,
            ':created_by' => $createdBy
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByHash(string $keyHash): ?array {
        $sql = "SELECT ak.*, akc.starts_at, akc.expires_at
                FROM access_keys ak
                INNER JOIN access_key_cycles akc ON akc.cycle_id = ak.cycle_id
                WHERE ak.key_hash = :key_hash
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':key_hash' => $keyHash
        ]);

        $accessKey = $stmt->fetch();

        return $accessKey !== false ? $accessKey : null;
    }

    public function findActiveByCycleAndRole(int $cycleId, string $role): ?array {
        $sql = "SELECT * FROM access_keys
                WHERE cycle_id = :cycle_id
                AND role = :role
                AND status = 'active'
                ORDER BY access_key_id DESC
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':cycle_id' => $cycleId,
            ':role' => $role
        ]);

        $accessKey = $stmt->fetch();

        return $accessKey !== false ? $accessKey : null;
    }

    public function findActiveByCycle(int $cycleId): array {
        $sql = "SELECT * FROM access_keys
                WHERE cycle_id = :cycle_id
                AND status = 'active'
                ORDER BY access_key_id ASC";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':cycle_id' => $cycleId
        ]);

        return $stmt->fetchAll();
    }

    public function markReplaced(int $accessKeyId, int $replacementAccessKeyId): bool {
        $sql = "UPDATE access_keys
                SET status = 'replaced', replaced_by = :replaced_by, replaced_at = NOW()
                WHERE access_key_id = :access_key_id
                AND status = 'active'";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':replaced_by' => $replacementAccessKeyId,
            ':access_key_id' => $accessKeyId
        ]);

        return $stmt->rowCount() === 1;
    }

    public function markCycleKeysExpired(int $cycleId): int {
        $sql = "UPDATE access_keys
                SET status = 'expired'
                WHERE cycle_id = :cycle_id
                AND status = 'active'";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':cycle_id' => $cycleId
        ]);

        return $stmt->rowCount();
    }
}

?>