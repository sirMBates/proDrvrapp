<?php

declare(strict_types=1);

namespace App\Validation;

final class AssignmentValidator {
    private const SAVE_UNLOCK_MINUTES = 120;

    public static function hasValidIdentity(array $assignment): bool {
        return (
            Validator::assignmentControl($assignment['assignment_control'] ??) &&
            Validator::positiveInteger($assignment['order_id'] ?? null) &&
            Validator::positiveInteger($assignment['driver_id'] ?? null)
        );
    }

    public static function isActive(array $assignment): bool {
        $status = strtolower(trim((string) ($assignment['assignment_status'] ?? '')));
        return (
            empty($assignment['completed_at']) &&
            empty($assignment['canceled_at']) &&
            !in_array(
                $status,
                ['completed', 'canceled'],
                true
            )
        );
    }

    public static function isPending(array $assignment): bool {
        return strtolower(trim((string) ($assignment['assignment_status'] ?? ''))) === 'pending';
    }

    public static function isConfirmed(array $assignment): bool {
        return strtolower(trim((string) ($assignment['assignment_status'] ?? ''))) === 'confirmed';
    }

    public static function isSaveWindowOpen(array $assignment, int $minutesBeforeStart = self::SAVE_UNLOCK_MINUTES): bool {
        $startDateTime = trim((string) ($assignment['start_date_time'] ?? ''));
        if (!Validator::dateTime($startDateTime) || $minutesBeforeStart < 0) {
            return false;
        }

        try {
            $timezone = new \DateTimeZone('America/New_York');
            $start = new \DateTimeImmutable(str_replace('T', ' ', $startDateTime), $timezone);
            $unlockTime = $start->modify("-{$minutesBeforeStart} minutes");

            $now = new \DateTimeImmutable('now', $timezone);
            return $now >= $unlockTime;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function hasStarted(array $assignment): bool {
        $startDateTime = trim((string) ($assignment['start_date_time'] ?? ''));

        if (!Validator::dateTime($startDateTime)) {
            return false;
        }

        try {
            $timezone = new \DateTimeZone('America/New_York');
            $start = new \DateTimeImmutable(str_replace('T', ' ', $startDateTime), $timezone);

            $now = new \DateTimeImmutable('now', $timezone);
            return $now >= $start;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function canConfirm(array $assignment): bool {
        return (
            self::hasValidIdentity($assignment) &&
            self::isActive($assignment) &&
            self::isPending($assignment)
        );
    }

    public static function canCancel(array $assignment): bool {
        return (
            self::hasValidIdentity($assignment) &&
            self::isActive($assignment) &&
            self::isPending($assignment)
        );
    }

    public static function canSave(array $assignment): bool {
        return (
            self::hasValidIdentity($assignment) &&
            self::isActive($assignment) &&
            self::isConfirmed($assignment) &&
            self::isSaveWindowOpen($assignment)
        );
    }

    public static function canComplete(array $assignment): bool {
        return (
            self::hasValidIdentity($assignment) &&
            self::isActive($assignment) &&
            self::isConfirmed($assignment) &&
            self::hasStarted($assignment)
        );
    }

    public static function requiresSignature(array $assignment): bool {
        return (int) ($assignment['signature_required'] ?? 0) === 1;
    }

    public static function hasPreTripSignature(array $data): bool {
        return Validator::imageDataUrl($data['pre_signature_base64'] ?? null);
    }

    public static function hasPostTripSignature(array $data): bool {
        return Validator::imageDataUrl($data['post_signature_base64'] ?? null);
    }

    public static function hasRequiredSignatures(array $data, array $assignment): bool {
        if (!self::requiresSignature($assignment)) {
            return true;
        }

        return (
            self::hasPreTripSignature($data) &&
            self::hasPostTripSignature($data)
        );
    }
}

?>