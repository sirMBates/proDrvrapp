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

    public function create(int $userId, string $operatorId, string $encryptedCdlNumber, string $cdlState, string $cdlExpirationDate, string $dotMedExpirationDate): int {
        $sql = "INSERT INTO driver_credentials (user_id, operator_id, cdl_number, cdl_state, cdl_status, cdl_expiration_date, dot_med_status, dot_med_expiration_date)
                VALUES (:user_id, :operator_id, :cdl_number, :cdl_state, :cdl_status, :cdl_expiration_date, :dot_med_status, :dot_med_expiration_date)";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':user_id' => $userId,
            ':operator_id' => $operatorId,
            ':cdl_number' => $encryptedCdlNumber,
            ':cdl_state' => $cdlState,
            ':cdl_status' => 'valid',
            ':cdl_expiration_date' => $cdlExpirationDate,
            ':dot_med_status' => 'valid',
            ':dot_med_expiration_date' => $dotMedExpirationDate
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