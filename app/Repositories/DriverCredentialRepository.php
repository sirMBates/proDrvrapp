<?php

declare(strict_types=1);

namespace App\Repositories;

use Core\Database;
use PDO;

class DriverCredentialRepository {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? (new Database())->connect();
    }

    public function create(int $userId, string $operatorId): int {
        $sql = "INSERT INTO driver_credentials (user_id, operator_id)
                VALUES (:user_id, :operator_id)";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':user_id' => $userId,
            ':operator_id' => $operatorId
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByOperatorId(string $operatorId): ?array {
        $sql = "SELECT *
                FROM driver_credentials
                WHERE operator_id = :operator_id
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':operator_id' => $operatorId
        ]);

        $credentials = $stmt->fetch();

        return $credentials !== false ? $credentials : null;
    }

    public function findByUserId(int $userId): ?array {
        $sql = "SELECT *
                FROM driver_credentials
                WHERE user_id = :user_id
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':user_id' => $userId
        ]);

        $credentials = $stmt->fetch();

        return $credentials !== false ? $credentials : null;
    }
}

?>