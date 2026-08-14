<?php

declare(strict_types=1);

namespace App\Repositories;
use Core\Database;

class DriverRepository {
    public function setDriver(string $username, string $email, string $password): int {
        $db = new Database();
        $pdo = $db->connect();

        try {
            $pdo->beginTransaction();
            $hashPassword = password_hash($password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO drivers (username, email, operator_id, password, first_name, last_name, mobile_number, birth_date, profile_picture) 
                VALUES (?,?,?,?,?,?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $username, $email, '', $hashPassword, '', '', '', null, null
            ]);

            $driverId = (int) $pdo->lastInsertId();
            $sql = "INSERT INTO pwd_reset (email, driver_id, reset_token, token_exp_time)
                    VALUES (?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email, $driverId, '', null]);
            $pdo->commit();

            return $driverId;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function checkDriver(string $username, string $email): bool {
        $db = new Database();
        $pdo = $db->connect();

        $sql = "SELECT username, email FROM drivers
                WHERE username = :username OR email = :email
                LIMIT 1";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':username' => $username,
            ':email' => $email
        ]);

        return $stmt->fetch() !== false;        
    }
}

?>