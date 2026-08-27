<?php

declare(strict_types=1);

namespace App\Repositories;
use Core\Database;
use PDO;

class DriverRepository {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? (new Database())->connect();
    }

    public function createDriver(string $username, string $email, string $password): int {
        $hashPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (username, email, password, first_name, last_name, mobile_number, birth_date, profile_picture) VALUES (?,?,?,?,?,?,?,?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $username, $email, $hashPassword, '', '', '', null, null
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function completeRegistration(int $driverId, string $encryptedFirstName, string $encryptedLastName, string $encryptedMobile, string $encryptedBirthDate): bool {
        $sql = "UPDATE users
                SET first_name = :first_name, last_name = :last_name,
                mobile_number = :mobile_number, birth_date = :birth_date
                WHERE driver_id = :driver_id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':first_name' => $encryptedFirstName,
            ':last_name' => $encryptedLastName,
            ':mobile_number' => $encryptedMobile,
            ':birth_date' => $encryptedBirthDate,
            ':driver_id' => $driverId
        ]);
    }

    public function findByUsername(string $username): ?array {
        $sql = "SELECT * FROM users
                WHERE username = :username
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':username' => $username
        ]);

        $driver = $stmt->fetch();
        return $driver !== false ? $driver : null;
    }

    public function findById(int $driverId): array {
        $sql = "SELECT * FROM users
                WHERE driver_id = :driver_id
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
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
        $sql = "SELECT * FROM users
                WHERE email = :email
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':email' => $email
        ]);

        $driver = $stmt->fetch();
        return $driver !== false ? $driver : null;
    }

    public function emailExistsForOtherDriver(int $driverId, string $email): bool {
        $sql = "SELECT driver_id
                FROM users
                WHERE email = :email AND driver_id <> :driver_id
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':email' => $email,
            ':driver_id' => $driverId
        ]);

        return $stmt->fetch() !== false;
    }

    public function checkDriver(string $username, string $email): bool {
        $sql = "SELECT username, email FROM users
                WHERE username = :username OR email = :email
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':username' => $username,
            ':email' => $email
        ]);

        return $stmt->fetch() !== false;        
    }

    public function updatePassword(int $driverId, string $password): bool {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = "UPDATE users
                SET password = :password
                WHERE driver_id = :driver_id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':password' => $hashedPassword,
            ':driver_id' => $driverId
        ]);
    }

    public function updateProfilePicture(int $driverId, string $storedPath): bool {
        $sql = "UPDATE users
                SET profile_picture = :profile_picture
                WHERE driver_id = :driver_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':profile_picture' => $storedPath,
            ':driver_id' => $driverId
        ]);
    }

    public function updateContactInformation(int $driverId, ?string $email, ?string $encryptedMobile): bool {
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

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}

?>