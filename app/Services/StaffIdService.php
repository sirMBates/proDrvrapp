<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\StaffRoleSequenceRepository;
use PDO;

class StaffIdService {
    private StaffRoleSequenceRepository $sequenceRepository;

    private const ROLE_CODES = [
        'driver' => 'DRV',
        'dispatch' => 'DSP',
        'admin' => 'ADM'
    ];

    public function __construct(PDO $pdo) {
        $this->sequenceRepository = new StaffRoleSequenceRepository($pdo);
    }

    public function allocate(string $role): string {
        if (!isset(self::ROLE_CODES[$role])) {
            throw new \InvalidArgumentException('Invalid role for staff ID allocation.');
        }

        $companyCode = strtoupper(trim((string) ($_ENV['COMPANY_CODE'] ?? 'PRODRVR')));

        if ($companyCode === '') {
            throw new \RuntimeException('Company code is not configured.');
        }

        $nextNumber = $this->sequenceRepository->getNextNumberForUpdate($role);

        $roleCode = self::ROLE_CODES[$role];
        $sequenceNumber = str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
        $staffId = $companyCode . '-' . $roleCode . '-' . $sequenceNumber;

        $this->sequenceRepository->advance($role);

        return $staffId;
    }
}

?>