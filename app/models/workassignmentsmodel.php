<?php

declare(strict_types=1);

use core\Database;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class WorkAssignments {
    private function normalizeAddressKey(string $address): string {
        $address = strtolower(trim((string)$address));
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

    private function getSharedNotesForAssignment(\PDO $pdo, string $customerName, string $originAddress, int $driverId): array {
        if ( empty($customerName) || empty($originAddress) ) {
            return [
                'shared_notes' => [],
                'current_driver_shared_note' => ''
            ];
        }

        $originKey = $this->normalizeAddressKey($originAddress);
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
            ':origin_key' => $originKey
        ]);

        $notes = $stmt->fetchAll();

        $currentDriverNote = '';

        foreach ($notes as $note) {
            if ( (string)$note['driver_id'] === (string)$driverId ) {
                $currentDriverNote = $note['note_body'];
                break;
            }
        }

        return [
            'shared_notes' => $notes,
            'current_driver_shared_note' => $currentDriverNote
        ];
    }

    protected function getWork(int $driverId): array {
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        $db = new Database;
        $pdo = $db->connect();
        $sql = "SELECT wo.*, d.operator_id, d.first_name, d.last_name, d.birth_date
                FROM work_orders wo INNER JOIN drivers d ON wo.driver_id = d.driver_id
                WHERE wo.driver_id = :driver_id AND wo.completed_at IS NULL AND wo.canceled_at IS NULL AND wo.assignment_status <> 'canceled'
                ORDER BY wo.start_date_time ASC, wo.order_id ASC";
        $stmt = $pdo->prepare($sql);
        $executed = $stmt->execute([
            ':driver_id' => $driverId
        ]);

        if (!$executed) {
            throw new \RuntimeException('Assignment query failed.');
        }

        $results = $stmt->fetchAll();
        unset($_SESSION['birth_date']);
        unset($_SESSION['signature_required']);
        $hasSignatureAssignment = false;
        forEach ($results as $assignment) {
            if ((int) ($assignment['signature_required'] ?? 0) === 1) {
                $hasSignatureAssignment = true;
                break;
            }
        }
        if ($hasSignatureAssignment) {
            $_SESSION['signature_required'] = 1;
        }

        foreach ($results as &$row) { // The (&) symbol makes $row a reference to each array el in $results, so changes persist.
            try {
                $row['first_name'] = Crypto::decrypt($row['first_name'], $key);
                $row['last_name'] = Crypto::decrypt($row['last_name'], $key);
                $row['birth_date'] = Crypto::decrypt($row['birth_date'], $key);
            } catch (\Throwable $exception) {
                $row['first_name'] = null;
                $row['last_name'] = null;
                $row['birth_date'] = null;
            }

            $row['assignment_status'] = $row['assignment_status'] ?? 'pending';

            try {
                // Attach shared notes for same customer + pickup/origin
                $noteData = $this->getSharedNotesForAssignment($pdo, (string) ($row['customer_name'] ?? ''), (string) ($row['origin'] ?? ''), $driverId);

                $row['shared_notes'] = $noteData['shared_notes'];
                $row['current_driver_shared_note'] = $noteData['current_driver_shared_note'];
            } catch (\Throwable $exception) {
                $row['shared_notes'] = [];
                $row['current_driver_shared_note'] = '';
            }

            $operatorBirthDate = $row['birth_date'];
            if (!empty($operatorBirthDate)) {
                $currentDate = date('md');
                $drvrDate = date('md', strtotime($operatorBirthDate));
                if ($currentDate === $drvrDate) {
                    $_SESSION['birth_date'] = $operatorBirthDate;
                }
            }
        }

        unset($row); // break the reference
        $stmt->closeCursor();
        return $results;
    }

    public function driverWorkAssignments (int $driverId): array {
        return $this->getWork($driverId);
    }
}

?>