<?php

declare(strict_types=1);

namespace App\Repositories;

use Core\Database;
use PDO;
use RuntimeException;

class DriverSharedNoteRepository {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? (new Database())->connect();
    }

    public function saveForAssignment(int $orderId, int $driverId, string $assignmentControl, string $noteBody): void {
        $noteBody = trim($noteBody);

        if ($noteBody === '') {
            return;
        }

        $assignmentSql = "SELECT customer_name, origin
                        FROM work_orders
                        WHERE assignment_control = :assignment_control
                        AND order_id = :order_id
                        AND driver_id = :driver_id
                        LIMIT 1";
        $assignmentStmt = $this->pdo->prepare($assignmentSql);

        $assignmentStmt->execute([
            ':assignment_control' => $assignmentControl,
            ':order_id' => $orderId,
            ':driver_id' => $driverId
        ]);

        $assignment = $assignmentStmt->fetch();

        if (!$assignment || empty($assignment['customer_name']) || empty($assignment['origin'])) {
            return;
        }

        $customerName = trim((string) $assignment['customer_name']);
        $originAddress = trim((string) $assignment['origin']);
        $originKey = $this->normalizeAddressKey($originAddress);

        $checkSql = "SELECT note_id
                    FROM driver_shared_notes
                    WHERE driver_id = :driver_id
                    AND customer_name = :customer_name
                    AND origin_address_key = :origin_key
                    AND is_active = 1
                    LIMIT 1";
        $checkStmt = $this->pdo->prepare($checkSql);

        $checkStmt->execute([
            ':driver_id' => $driverId,
            ':customer_name' => $customerName,
            ':origin_key' => $originKey
        ]);

        $existingNote = $checkStmt->fetch();

        if ($existingNote) {
            $updateSql = "UPDATE driver_shared_notes
                        SET note_body = :note_body,
                        updated_at = NOW()
                        WHERE note_id = :note_id";
            $updateStmt = $this->pdo->prepare($updateSql);

            $updated = $updateStmt->execute([
                ':note_body' => $noteBody,
                ':note_id' => $existingNote['note_id']
            ]);

            if (!$updated) {
                throw new RuntimeException('Shared assignment note could not be updated.');
            }

            return;
        }

        $insertSql = "INSERT INTO driver_shared_notes (driver_id, customer_name, origin_address, origin_address_key, note_body, is_active, created_at, updated_at)
                    VALUES (:driver_id, :customer_name, :origin_address, :origin_key, :note_body, 1, NOW(), NOW())";
        $insertStmt = $this->pdo->prepare($insertSql);

        $inserted = $insertStmt->execute([
            ':driver_id' => $driverId,
            ':customer_name' => $customerName,
            ':origin_address' => $originAddress,
            ':origin_key' => $originKey,
            ':note_body' => $noteBody
        ]);

        if (!$inserted) {
            throw new RuntimeException('Shared assignment note could not be saved.');
        }
    }

    private function normalizeAddressKey(string $address): string {
        $address = strtolower(trim($address));
        $address = preg_replace('/[^a-z0-9\s]/', '', $address) ?? '';
        $address = preg_replace('/\s+/', ' ', $address) ?? '';

        $replace = [
            ' street' => ' st',
            ' avenue' => ' ave',
            ' road' => ' rd',
            ' boulevard' => ' blvd',
            ' drive' => ' dr',
            ' lane' => ' ln',
            ' court' => ' ct',
            ' place' => ' pl',
            ' circle' => ' cir'
        ];

        return str_replace(array_keys($replace), array_values($replace), $address);
    }
}

?>