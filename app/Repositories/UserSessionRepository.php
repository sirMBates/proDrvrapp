<?php

declare(strict_types=1);

namespace App\Repositories;

use Core\Database;
use PDO;

class UserSessionRepository {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? (new Database())->connect();
    }

    public function create(string $sessionHash, int $userId, string $expiresAt): void {
        $sql = "INSERT INTO user_sessions (session_id_hash, user_id, expires_at) 
                VALUES (:session_hash, :user_id, :expires_at)";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':session_hash' => $sessionHash,
            ':user_id' => $userId,
            ':expires_at' => $expiresAt
        ]);
    }

    public function findBySessionHash(string $sessionHash): ?array {
        $sql = "SELECT session_id_hash, user_id, last_activity_at, expires_at, revoked_at
                FROM user_sessions
                WHERE session_id_hash = :session_hash
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':session_hash' => $sessionHash
        ]);

        $session = $stmt->fetch();

        return $session !== false ? $session : null;
    }

    public function findActiveByUserId(int $userId): array {
        $sql = "SELECT session_id_hash, user_id, last_activity_at, expires_at
                FROM user_sessions
                WHERE user_id = :user_id
                    AND revoked_at IS NULL
                    AND expires_at > CURRENT_TIMESTAMP
                ORDER BY last_activity_at DESC";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->fetchAll();
    }

    public function touch(string $sessionHash, string $expiresAt): bool {
        $sql = "UPDATE user_sessions
                SET last_activity_at = CURRENT_TIMESTAMP,
                    expires_at = :expires_at
                WHERE session_id_hash = :session_hash
                    AND revoked_at IS NULL
                    AND expires_at > CURRENT_TIMESTAMP";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':session_hash' => $sessionHash,
            ':expires_at' => $expiresAt
        ]);

        return $stmt->rowCount() > 0;
    }

    public function revoke(string $sessionHash): bool {
        $sql = "UPDATE user_sessions
                SET revoked_at = CURRENT_TIMESTAMP
                WHERE session_id_hash = :session_hash
                    AND revoked_at IS NULL";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':session_hash' => $sessionHash
        ]);

        return $stmt->rowCount() > 0;
    }

    public function revokeOthers(int $userId, string $currentSessionHash): int {
        $sql = "UPDATE user_sessions
                SET revoked_at = CURRENT_TIMESTAMP
                WHERE user_id = :user_id
                    AND session_id_hash <> :session_hash
                    AND revoked_at IS NULL";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':user_id' => $userId,
            ':session_hash' => $currentSessionHash
        ]);

        return $stmt->rowCount();
    }

    public function countActiveDrivers(): int {
        $sql = "SELECT COUNT(DISTINCT us.user_id)
                FROM user_sessions AS us
                INNER JOIN users AS u
                    ON u.user_id = us.user_id
                WHERE u.role = 'driver'
                    AND u.account_status = 'active'
                    AND us.revoked_at IS NULL
                    AND us.expires_at > CURRENT_TIMESTAMP";
        $stmt = $this->pdo->query($sql);

        return (int) $stmt->fetchColumn();
    }
}

?>