<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

final class AssignmentOperationalGate {
    public function assertGarageReportReached(array $assignment): void {
        if (!$this->isEnforced()) {
            return;
        }

        $startDateTime = trim((string) ($assignment['start_date_time'] ?? ''));

        if ($startDateTime === '') {
            throw new RuntimeException('Assignment Garage Report Time is unavailable.');
        }

        try {
            $garageReportTime = new DateTimeImmutable($startDateTime);
            $currentTime = new DateTimeImmutable();
        } catch (\Exception) {
            throw new RuntimeException('Assignment Garage Report Time is invalid.');
        }

        if ($currentTime < $garageReportTime) {
            throw new InvalidArgumentException('This assignment is not available until Garage Report Time.');
        }
    }

    private function isEnforced(): bool {
        return filter_var($_ENV['ENFORCE_ASSIGNMENT_START_GATE'] ?? true, FILTER_VALIDATE_BOOL);
    }
}

?>