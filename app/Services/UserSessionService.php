<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserSessionRepository;
use DateTimeImmutable;
use InvalidArgumentException;

class UserSessionService {
    private UserSessionRepository $sessionRepository;

    public function __construct(?UserSessionRepository $sessionRepository = null) {
        $this->sessionRepository = $sessionRepository ?? new UserSessionRepository();
    }

    public function register(int $userId, int $lifetimeSeconds): void {
        if ($userId < 1 || $lifetimeSeconds < 1) {
            throw new InvalidArgumentException('Invalid session registration parameters.');
        }

        $sessionHash = $this->currentSessionHash();
        $expiresAt = $this->expirationDate($lifetimeSeconds);

        $this->sessionRepository->create($sessionHash, $userId, $expiresAt);
    }

    public function findCurrent(): ?array {
        return $this->sessionRepository->findBySessionHash($this->currentSessionHash());
    }

    public function refresh(int $lifetimeSeconds): bool {
        if ($lifetimeSeconds < 1) {
            throw new InvalidArgumentException('Invalid session lifetime.');
        }

        return $this->sessionRepository->touch($this->currentSessionHash(), $this->expirationDate($lifetimeSeconds));
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
        return (new DateTimeImmutable())->modify("+{$lifetimeSeconds} seconds")->format('Y-m-d H:i:s');
    }
}

?>