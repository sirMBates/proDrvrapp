<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AssignmentRepository;
use App\Repositories\SharedNoteRepository;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class WorkAssignmentService {
    private AssignmentRepository $assignmentRepository;
    private SharedNoteRepository $sharedNoteRepository;

    public function __construct() {
        $this->assignmentRepository = new AssignmentRepository();
        $this->sharedNoteRepository = new SharedNoteRepository();
    }

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

    private function getSharedNotesForAssignment(string $customerName, string $originAddress, int $driverId): array {
        if ($customerName === '' || $originAddress === '') {
            return [
                'shared_notes' => [],
                'current_driver_shared_note' => ''
            ];
        }

        $originKey = $this->normalizeAddressKey($originAddress);
        $notes = $this->sharedNoteRepository->findActiveByCustomerAndOrigin($customerName, $originKey);
        $currentDriverNote = '';

        foreach ($notes as $note) {
            if ((int) $note['driver_id'] === $driverId ) {
                $currentDriverNote = (string) $note['note_body'];
                break;
            }
        }

        return [
            'shared_notes' => $notes,
            'current_driver_shared_note' => $currentDriverNote
        ];
    }

    private function prepareAssignments(int $driverId): array {
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        $results = $this->assignmentRepository->findActiveAssignmentsByDriver($driverId);

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
                $noteData = $this->getSharedNotesForAssignment((string) ($row['customer_name'] ?? ''), (string) ($row['origin'] ?? ''), $driverId);

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
        return $results;
    }

    public function driverAssignments (int $driverId): array {
        return $this->prepareAssignments($driverId);
    }
}

?>