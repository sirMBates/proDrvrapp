<?php

declare(strict_types=1);

namespace App\Repositories;

use Core\Database;

class SharedNoteRepository {
    public function findActiveByCustomerAndOrigin(string $customerName, string $originKey): array {
        $db = new Database();
        $pdo = $db->connect();

        $sql = "SELECT note_id, driver_id, customer_name,
                origin_address, note_body, updated_at
                FROM driver_shared_notes
                WHERE customer_name = :customer_name
                    AND origin_address_key = :origin_key
                    AND is_active = 1
                ORDER BY updated_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':customer_name' => trim($customerName),
            ':origin_key' => trim($originKey)
        ]);

        return $stmt->fetchAll();
    }
}

?>