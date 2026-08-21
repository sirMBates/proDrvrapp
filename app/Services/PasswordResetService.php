<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PasswordResetRepository;
use App\Repositories\DriverRepository;

class PasswordResetService {
    private PasswordResetRepository $passwordResetRepository;
    private DriverRepository $driverRepository;
    private int $resetLifetimeMinutes;

    public function __construct() {
        $this->passwordResetRepository = new PasswordResetRepository();
        $this->driverRepository = new DriverRepository();
        $this->resetLifetimeMinutes = (int) ($_ENV['PASSWORD_RESET_LIFETIME_MINUTES'] ?? 15);
    }

    public function createResetRequest(string $email): ?array {
        $driver = $this->driverRepository->findByEmail($email);
        if ($driver === null) {
            return null;
        }

        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = date('Y-m-d H:i:s', time() + ($this->resetLifetimeMinutes * 60));

        $resetId = $this->passwordResetRepository->createRequest((int) $driver['driver_id'], $tokenHash, $expiresAt);

        return [
            'resetId' => $resetId,
            'rawToken' => $rawToken,
            'email' => $driver['email'],
            'expiresAt' => $expiresAt
        ];
    }

    public function validateToken(string $rawToken): ?array {
        if ($rawToken === '') {
            return null;
        }

        $tokenHash = hash('sha256', $rawToken);
        $resetRequest = $this->passwordResetRepository->findByTokenHash($tokenHash);
        if ($resetRequest === null) {
            return null;
        }

        if ($resetRequest['used_at'] !== null) {
            return null;
        }

        $expiresAt = strtotime($resetRequest['expires_at']);
        if ($expiresAt === false || $expiresAt <= time()) {
            return null;
        }

        return $resetRequest;
    }

    public function completePasswordReset(string $rawToken, string $newPassword): bool {
        $resetRequest = $this->validateToken($rawToken);
        if ($resetRequest === null) {
            return false;
        }

        $driverId = (int) $resetRequest['driver_id'];
        $passwordUpdated = $this->driverRepository->updatePassword($driverId, $newPassword);
        if (!$passwordUpdated) {
            return false;
        }

        return $this->passwordResetRepository->markUsed((int) $resetRequest['reset_id']);
    }
}

?>