<?php

use core\Database;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Dotenv\Dotenv;
require_once "../vendor/autoload.php";
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.local.env');
$dotenv->load();

class WorkAssignments {
    private function normalizeAddressKey($address) {
        $address = strtolower(trim((string)$address));
        $address = preg_replace('/[^a-z0-9\s]/', '', $address);
        $address = preg_replace('/\s+/', ' ', $address);

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

    private function getSharedNotesForAssignment($pdo, $customerName, $originAddress, $drvrid) {
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
            if ( (string)$note['driver_id'] === (string)$drvrid ) {
                $currentDriverNote = $note['note_body'];
                break;
            }
        }

        return [
            'shared_notes' => $notes,
            'current_driver_shared_note' => $currentDriverNote
        ];
    }

    protected function getWork($drvrid) {
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        $db = new Database;
        $pdo = $db->connect();
        $sql = "SELECT wo.*, d.operator_id, d.first_name, d.last_name, d.birth_date
                FROM work_orders wo INNER JOIN drivers d ON wo.driver_id = d.driver_id
                WHERE wo.driver_id = :driver_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':driver_id', $drvrid);
        $stmt->execute();

        if (!$stmt) {
            throw new Exception("Driver not found");
        }

        $results = $stmt->fetchAll();

        foreach ($results as &$row) { // The (&) symbol makes $row a reference to each array el in $results, so changes persist.
            try {
                $row['first_name'] = Crypto::decrypt($row['first_name'], $key);
                $row['last_name'] = Crypto::decrypt($row['last_name'], $key);
                $row['birth_date'] = Crypto::decrypt($row['birth_date'], $key);

                $operatorBirthDate = $row['birth_date'];

                if (!empty($row['signature_required']) && $row['signature_required'] === 1) {
                    $_SESSION['signature_required'] = $row['signature_required'];
                }

                if (empty($row['confirmed_assignment'])) {
                    $row['confirmed_assignment'] = 'unconfirmed';
                }

                // Attach shared notes for same customer + pickup/origin
                $noteData = $this->getSharedNotesForAssignment($pdo, $row['customer_name'] ?? '', $row['origin'] ?? '', $drvrid);

                $row['shared_notes'] = $noteData['shared_notes'];
                $row['current_driver_shared_note'] = $noteData['current_driver_shared_note'];

                $currentDate = date('md');
                $drvrDate = date('md', strtotime($operatorBirthDate));
                if ($currentDate === $drvrDate) {
                    $_SESSION['birth_date'] = $operatorBirthDate;
                }
            } catch (\Exception $e) {
                // Handle corrupted or missing ciphertext
                $row['first_name'] = null;
                $row['last_name'] = null;
                $row['birth_date'] = null;
                $row['shared_notes'] = [];
                $row['current_driver_shared_note'] = '';
            }
        }

        unset($row); // break the reference
        $stmt->closeCursor();
        return $results;
    }

    public function driverWorkAssignments ($drvrid) {
        return $this->getWork($drvrid);
    }
}

?>