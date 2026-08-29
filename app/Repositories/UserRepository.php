<?php

declare(strict_types=1);

namespace App\Repositories;
use Core\Database;
use PDO;

class UserRepository {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? (new Database())->connect();
    }

    public function createUser(string $username, string $email, string $password): int {
        $hashPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (username, email, password, first_name, last_name, mobile_number, birth_date, profile_picture) VALUES (?,?,?,?,?,?,?,?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $username, $email, $hashPassword, '', '', '', null, null
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function completeRegistration(int $userId, string $encryptedFirstName, string $encryptedLastName, string $encryptedMobile, string $encryptedBirthDate): bool {
        $sql = "UPDATE users
                SET first_name = :first_name, last_name = :last_name,
                mobile_number = :mobile_number, birth_date = :birth_date
                WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':first_name' => $encryptedFirstName,
            ':last_name' => $encryptedLastName,
            ':mobile_number' => $encryptedMobile,
            ':birth_date' => $encryptedBirthDate,
            ':user_id' => $userId
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

        $user = $stmt->fetch();
        return $user !== false ? $user : null;
    }

    public function findById(int $userId): array {
        $sql = "SELECT * FROM users
                WHERE user_id = :user_id
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId
        ]);
        
        $user = $stmt->fetch();
        if (!$user) {
            throw new \RuntimeException('User not found.');
        }
        return $user;
    }

    public function findByEmail(string $email): ?array {
        $sql = "SELECT * FROM users
                WHERE email = :email
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':email' => $email
        ]);

        $user = $stmt->fetch();
        return $user !== false ? $user : null;
    }

    public function emailExistsForOtherUser(int $userId, string $email): bool {
        $sql = "SELECT user_id
                FROM users
                WHERE email = :email AND user_id <> :user_id
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':email' => $email,
            ':user_id' => $userId
        ]);

        return $stmt->fetch() !== false;
    }

    public function checkUser(string $username, string $email): bool {
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

    public function updatePassword(int $userId, string $password): bool {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = "UPDATE users
                SET password = :password
                WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':password' => $hashedPassword,
            ':user_id' => $userId
        ]);
    }

    public function updateProfilePicture(int $userId, string $storedPath): bool {
        $sql = "UPDATE users
                SET profile_picture = :profile_picture
                WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':profile_picture' => $storedPath,
            ':user_id' => $userId
        ]);
    }

    public function updateContactInformation(int $userId, ?string $email, ?string $encryptedMobile): bool {
        $fields = [];
        $params = [
            ':user_id' => $userId
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
                WHERE user_id = :user_id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}

?>