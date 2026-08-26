<?php

declare(strict_types=1);

namespace App\Repositories;
use Core\Database;

class DriverRepository {
    public function createDriver(string $username, string $email, string $password): int {
        $db = new Database();
        $pdo = $db->connect();

        $hashPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (username, email, operator_id, password, first_name, last_name, mobile_number, birth_date, profile_picture) 
            VALUES (?,?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $username, $email, '', $hashPassword, '', '', '', null, null
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function completeRegistration(int $driverId, string $operatorId, string $encryptedFirstName, string $encryptedLastName, string $encryptedMobile, string $encryptedBirthDate): bool {
        $db = new Database();
        $pdo = $db->connect();

        $sql = "UPDATE users
                SET operator_id = :operator_id, first_name = :first_name,
                last_name = :last_name, mobile_number = :mobile_number, birth_date = :birth_date
                WHERE driver_id = :driver_id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ':operator_id' => $operatorId,
            ':first_name' => $encryptedFirstName,
            ':last_name' => $encryptedLastName,
            ':mobile_number' => $encryptedMobile,
            ':birth_date' => $encryptedBirthDate,
            ':driver_id' => $driverId
        ]);
    }

    public function findByUsername(string $username): ?array {
        $db = new Database();
        $pdo = $db->connect();

        $sql = "SELECT * FROM users
                WHERE username = :username
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':username' => $username
        ]);

        $driver = $stmt->fetch();
        return $driver !== false ? $driver : null;
    }

    public function findById(int $driverId): array {
        $db = new Database();
        $pdo = $db->connect();

        $sql = "SELECT * FROM users
                WHERE driver_id = :driver_id
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':driver_id' => $driverId
        ]);
        
        $driver = $stmt->fetch();
        if (!$driver) {
            throw new \RuntimeException('Driver not found.');
        }
        return $driver;
    }

    public function findByEmail(string $email): ?array {
        $db = new Database();
        $pdo = $db->connect();

        $sql = "SELECT * FROM users
                WHERE email = :email
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':email' => $email
        ]);

        $driver = $stmt->fetch();
        return $driver !== false ? $driver : null;
    }

    public function emailExistsForOtherDriver(int $driverId, string $email): bool {
        $db = new Database();
        $pdo = $db->connect();

        $sql = "SELECT driver_id
                FROM users
                WHERE email = :email AND driver_id <> :driver_id
                LIMIT 1";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':email' => $email,
            ':driver_id' => $driverId
        ]);

        return $stmt->fetch() !== false;
    }

    public function checkDriver(string $username, string $email): bool {
        $db = new Database();
        $pdo = $db->connect();

        $sql = "SELECT username, email FROM users
                WHERE username = :username OR email = :email
                LIMIT 1";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':username' => $username,
            ':email' => $email
        ]);

        return $stmt->fetch() !== false;        
    }

    public function updatePassword(int $driverId, string $password): bool {
        $db = new Database();
        $pdo = $db->connect();

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = "UPDATE users
                SET password = :password
                WHERE driver_id = :driver_id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ':password' => $hashedPassword,
            ':driver_id' => $driverId
        ]);
    }

    public function updateProfilePicture(int $driverId, string $storedPath): bool {
        $db = new Database();
        $pdo = $db->connect();

        $sql = "UPDATE users
                SET profile_picture = :profile_picture
                WHERE driver_id = :driver_id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':profile_picture' => $storedPath,
            ':driver_id' => $driverId
        ]);
    }

    public function updateContactInformation(int $driverId, ?string $email, ?string $encryptedMobile): bool {
        $db = new Database();
        $pdo = $db->connect();

        $fields = [];
        $params = [
            ':driver_id' => $driverId
        ];

        if ($email !== null && $email !== '') {
            $fields[] = 'email = :email';
            $params[':email'] = $email;
        }

        if ($encryptedMobile !== null && $encryptedMobile !== '') {
            $fields[] = 'mobile_number = :mobile_number';
            $params[':mobile_number'] = $encryptedMobile;
        }

        if ($fields === []) {
            return false;
        }

        $sql = "UPDATE users
                SET " . implode(', ', $fields) . "
                WHERE driver_id = :driver_id";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }
}

?>