<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserSessionRepository;
use DateTimeImmutable;
use InvalidArgumentException;

class UserSessionService {
    private const IDLE_TIMEOUT_SECONDS = 2 * 60 * 60;
    private const ABSOLUTE_TIMEOUT_SECONDS = 8 * 60 * 60;
    private UserSessionRepository $sessionRepository;

    public function __construct(?UserSessionRepository $sessionRepository = null) {
        $this->sessionRepository = $sessionRepository ?? new UserSessionRepository();
    }

    public function register(int $userId): void {
        if ($userId < 1) {
            throw new InvalidArgumentException('Invalid session registration parameters.');
        }

        $sessionHash = $this->currentSessionHash();
        $expiresAt = $this->expirationDate(self::IDLE_TIMEOUT_SECONDS);
        $absoluteExpiresAt = $this->expirationDate(self::ABSOLUTE_TIMEOUT_SECONDS);
        $this->sessionRepository->create($sessionHash, $userId, $expiresAt, $absoluteExpiresAt);
    }

    public function authorizeDriverSession(int $userId, bool $transferConfirmed = false): bool {
        if ($userId < 1) {
            throw new \InvalidArgumentException('Invalid user ID.');
        }

        $this->sessionRepository->beginTransaction();

        try {
            if (!$this->sessionRepository->lockUserForSessionUpdate($userId)) {
                throw new \DomainException('Driver account is not eligible for authentication.');
            }

            $existingSessions = $this->sessionRepository->findActiveByUserId($userId);

            if ($existingSessions !== [] && !$transferConfirmed) {
                $this->sessionRepository->rollBack();
                return false;
            }

            $sessionHash = $this->currentSessionHash();

            if ($transferConfirmed) {
                $this->sessionRepository->revokeOthers($userId, $sessionHash);
            }

            $this->sessionRepository->create($sessionHash, $userId, $this->expirationDate(self::IDLE_TIMEOUT_SECONDS), $this->expirationDate(self::ABSOLUTE_TIMEOUT_SECONDS));

            $this->sessionRepository->commit();
            return true;
        } catch (\Throwable $exception) {
            $this->sessionRepository->rollBack();
            throw $exception;
        }
    }

    public function validatePendingTransfer(array $pendingTransfer): int {
        $userId = filter_var($pendingTransfer['user_id'] ?? null, FILTER_VALIDATE_INT);
        $createdAt = filter_var($pendingTransfer['created_at'] ?? null, FILTER_VALIDATE_INT);
        $expiresAt = filter_var($pendingTransfer['expires_at'] ?? null, FILTER_VALIDATE_INT);

        $now = time();

        if ($userId === false || $createdAt === false || $expiresAt === false || $userId < 1 || $createdAt > $now || $expiresAt <= $now || $expiresAt <= $createdAt || ($expiresAt - $createdAt) > 300) {
            throw new \DomainException('Your device-transfer request has expired or is invalid. Please sign in again.');
        }

        return $userId;
    }

    public function findCurrent(): ?array {
        return $this->sessionRepository->findBySessionHash($this->currentSessionHash());
    }

    public function isCurrentValid(int $userId): bool {
        if ($userId < 1) {
            return false;
        }

        $session = $this->findCurrent();
        if ($session === null) {
            return false;
        }

        if ((int) $session['user_id'] !== $userId) {
            return false;
        }

        if ($session['revoked_at'] !== null) {
            return false;
        }

        if (empty($session['absolute_expires_at'])) {
            return false;
        }

        $utc = new \DateTimeZone('UTC');

        $now = new \DateTimeImmutable('now', $utc);
        $idleExpiresAt = new \DateTimeImmutable($session['expires_at'], $utc);
        $absoluteExpiresAt = new \DateTimeImmutable($session['absolute_expires_at'], $utc);

        return $now < $idleExpiresAt && $now < $absoluteExpiresAt;
    }

    public function hasActiveSession(int $userId): bool {
        if ($userId < 1) {
            throw new \InvalidArgumentException('Invalid user ID.');
        }

        return $this->sessionRepository->findActiveByUserId($userId) !== [];
    }

    public function refresh(): bool {
        $session = $this->findCurrent();
        if ($session === null || $session['revoked_at'] !== null || $session['absolute_expires_at'] === null) {
            return false;
        }

        $utc = new \DateTimeZone('UTC');

        $now = new \DateTimeImmutable('now', $utc);
        $idleExpiresAt = new \DateTimeImmutable($session['expires_at'], $utc);
        $absoluteExpiresAt = new \DateTimeImmutable($session['absolute_expires_at'], $utc);

        if ($now >= $idleExpiry || $now >= $absoluteExpiry) {
            return false;
        }

        $newIdleExpiry = $now->modify('+' . self::IDLE_TIMEOUT_SECONDS . ' seconds');
        if ($newIdleExpiry > $absoluteExpiry) {
            $newIdleExpiry = $absoluteExpiry;
        }

        return $this->sessionRepository->touch($this->currentSessionHash(), $newIdleExpiry->format('Y-m-d H:i:s'));
    }

    public function revokeCurrent(): bool {
        return $this->sessionRepository->revoke($this->currentSessionHash());
    }

    public function countActiveDrivers(): int {
        return $this->sessionRepository->countActiveDrivers();
    }

    private function currentSessionHash(): string {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new \RuntimeException('An active PHP session is required.');
        }

        $sessionId = session_id();

        if ($sessionId === '') {
            throw new \RuntimeException('A valid PHP session identifier is required.');
        }

        return hash('sha256', $sessionId);
    }

    private function expirationDate(int $lifetimeSeconds): string {
        return (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->modify("+{$lifetimeSeconds} seconds")->format('Y-m-d H:i:s');
    }
}

?>