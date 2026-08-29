<?php

declare(strict_types=1);

namespace App\Repositories;

use Core\Database;

class PasswordResetRepository {
    public function createRequest(int $userId, string $tokenHash, string $expiresAt): int {
        $db = new Database();
        $pdo = $db->connect();

        $sql = "INSERT INTO password_reset (user_id, token_hash, expires_at)
                VALUES (:user_id, :token_hash, :expires_at)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':token_hash' => $tokenHash,
            ':expires_at' => $expiresAt
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function findByTokenHash(string $tokenHash): ?array {
        $db = new Database();
        $pdo = $db->connect();

        $sql = "SELECT reset_id, user_id, token_hash, expires_at, used_at, created_at
                FROM password_reset
                WHERE token_hash = :token_hash
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':token_hash' => $tokenHash
        ]);

        $resetRequest = $stmt->fetch();
        return $resetRequest !== false ? $resetRequest : null;
    }

    public function markUsed(int $resetId): bool {
        $db = new Database();
        $pdo = $db->connect();

        $sql = "UPDATE password_reset
                SET used_at = CURRENT_TIMESTAMP
                WHERE reset_id = :reset_id AND used_at IS NULL";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':reset_id' => $resetId
        ]);
    }
}

?>