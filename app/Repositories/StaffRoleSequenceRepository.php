<?php

declare(strict_types=1);

namespace App\Repositories;

use Core\Database;
use PDO;

class StaffRoleSequenceRepository {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? (new Database())->connect();
    }

    public function getNextNumberForUpdate(string $role): int {
        $sql = "SELECT next_number FROM staff_role_sequences
                WHERE role = :role
                LIMIT 1
                FOR UPDATE";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':role' => $role
        ]);

        $nextNumber = $stmt->fetchColumn();

        if ($nextNumber === false) {
            throw new \RuntimeException("Staff ID sequence not found for role: {$role}");
        }

        return (int) $nextNumber;
    }

    public function advance(string $role): bool {
        $sql = "UPDATE staff_role_sequences
                SET next_number = next_number + 1
                WHERE role = :role";
        $stmt = $this->pdo->prepare($sql);

        $executed = $stmt->execute([
            ':role' => $role
        ]);

        if (!$executed || $stmt->rowCount() !== 1) {
            throw new \RuntimeException("Unable to advance staff ID sequence for role: {$role}");
        }

        return true;
    }
}

?>