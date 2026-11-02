<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AccessKeyCycleRepository;
use App\Repositories\AccessKeyRepository;
use Core\Database;
use DateTimeImmutable;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use PDO;

class AccessKeyService {
    private PDO $pdo;
    private AccessKeyCycleRepository $cycleRepository;
    private AccessKeyRepository $accessKeyRepository;
    private Key $encryptionKey;

    private const ROLES = [
        'driver',
        'dispatch',
        'admin'
    ];

    private const CYCLE_LENGTH_DAYS = 180;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? (new Database())->connect();

        $this->cycleRepository = new AccessKeyCycleRepository($this->pdo);
        $this->accessKeyRepository = new AccessKeyRepository($this->pdo);

        $this->encryptionKey = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
    }

    public function createInitialCycle(?int $createdBy = null): array {
        if ($this->cycleRepository->findCurrent() !== null) {
            throw new \RuntimeException('An active access key cycle already exists.');
        }

        $startsAt = new DateTimeImmutable();
        $expiresAt = $startsAt->modify('+' . (self::CYCLE_LENGTH_DAYS - 1) . ' days')->setTime(23, 59, 59);

        $this->pdo->beginTransaction();

        try {
            $cycleId = $this->cycleRepository->create($startsAt->format('Y-m-d H:i:s'), $expiresAt->format('Y-m-d H:i:s'), $createdBy);

            $keys = [];

            foreach (self::ROLES as $role) {
                $keys[$role] = $this->createKey($cycleId, $role, $createdBy);
            }

            $this->pdo->commit();

            return [
                'cycle_id' => $cycleId,
                'starts_at' => $startsAt->format('Y-m-d H:i:s'),
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'keys' => $keys
            ];
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function resolveRegistrationKey(string $accessKey): array {
        $normalizedKey = $this->normalizeKey($accessKey);

        if (!$this->isValidFormat($normalizedKey)) {
            throw new \InvalidArgumentException('Invalid access key.');
        }

        $keyHash = $this->hashKey($normalizedKey);

        $storedKey = $this->accessKeyRepository->findByHash($keyHash);

        if ($storedKey === null) {
            throw new \InvalidArgumentException('Invalid access key.');
        }

        if ($storedKey['status'] !== 'active') {
            throw new \InvalidArgumentException('Invalid access key.');
        }

        $now = new DateTimeImmutable();
        $startsAt = new DateTimeImmutable($storedKey['starts_at']);
        $expiresAt = new DateTimeImmutable($storedKey['expires_at']);

        if ($now < $startsAt || $now > $expiresAt) {
            throw new \InvalidArgumentException('Invalid access key.');
        }

        return [
            'access_key_id' => (int) $storedKey['access_key_id'],
            'cycle_id' => (int) $storedKey['cycle_id'],
            'role' => $storedKey['role'],

            // Future multi-company support:
            // 'company_id' => (int) $storedKey['company_id'],
        ];
    }

    public function replaceKey(string $role, ?int $createdBy = null): array {
        $this->assertValidRole($role);

        $cycle = $this->cycleRepository->findCurrent();

        if ($cycle === null) {
            throw new \RuntimeException('No active access key cycle exists.');
        }

        $currentKey = $this->accessKeyRepository->findActiveByCycleAndRole((int) $cycle['cycle_id'], $role);

        if ($currentKey === null) {
            throw new \RuntimeException('No active access key exists for this role.');
        }

        $this->pdo->beginTransaction();

        try {
            $replacement = $this->createKey((int) $cycle['cycle_id'], $role, $createdBy);

            $replaced = $this->accessKeyRepository->markReplaced((int) $currentKey['access_key_id'], $replacement['access_key_id']);

            if (!$replaced) {
                throw new \RuntimeException('Unable to replace access key.');
            }

            $this->pdo->commit();

            return [
                'access_key_id' => $replacement['access_key_id'],
                'cycle_id' => (int) $cycle['cycle_id'],
                'role' => $role,
                'key' => $replacement['key'],
                'expires_at' => $cycle['expires_at']
            ];
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function rotateCycle(?int $createdBy = null): array {
        $currentCycle = $this->cycleRepository->findCurrent();

        if ($currentCycle !== null) {
            throw new \RuntimeException('The current access key cycle has not expired.');
        }

        $latestCycle = $this->cycleRepository->findLatest();

        if ($latestCycle === null) {
            throw new \RuntimeException('No previous access key cycle exists.');
        }

        $expiresAt = new DateTimeImmutable($latestCycle['expires_at']);
        $now = new DateTimeImmutable();

        if ($now <= $expiresAt) {
            throw new \RuntimeException('The current access key cycle has not expired.');
        }

        $startsAt = $now->setTime(0, 0, 0);
        $newExpiresAt = $startsAt->modify('+' . (self::CYCLE_LENGTH_DAYS - 1) . ' days')->setTime(23, 59, 59);

        $this->pdo->beginTransaction();

        try {
            $this->accessKeyRepository->markCycleKeysExpired((int) $latestCycle['cycle_id']);

            $cycleId = $this->cycleRepository->create($startsAt->format('Y-m-d H:i:s'), $newExpiresAt->format('Y-m-d H:i:s'), $createdBy);

            $keys = [];

            foreach (self::ROLES as $role) {
                $keys[$role] = $this->createKey($cycleId, $role, $createdBy);
            }

            $this->pdo->commit();

            return [
                'cycle_id' => $cycleId,
                'starts_at' => $startsAt->format('Y-m-d H:i:s'),
                'expires_at' => $newExpiresAt->format('Y-m-d H:i:s'),
                'keys' => $keys
            ];
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    private function createKey(int $cycleId, string $role, ?int $createdBy = null): array {
        $this->assertValidRole($role);

        do {
            $plainKey = $this->generateKey();
            $keyHash = $this->hashKey($plainKey);
        } while ($this->accessKeyRepository->findByHash($keyHash) !== null);

        $encryptedKey = Crypto::encrypt($plainKey, $this->encryptionKey);

        $accessKeyId = $this->accessKeyRepository->create($cycleId, $role, $keyHash, $encryptedKey, $createdBy);

        return [
            'access_key_id' => $accessKeyId,
            'role' => $role,
            'key' => $plainKey
        ];
    }

    private function generateKey(): string {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ0123456789';

        $first = '';
        $second = '';

        $max = strlen($characters) - 1;

        for ($i = 0; $i < 4; $i++) {
            $first .= $characters[random_int(0, $max)];
            $second .= $characters[random_int(0, $max)];
        }

        return $first . '-' . $second;
    }

    private function normalizeKey(string $accessKey): string {
        return strtoupper(trim($accessKey));
    }

    private function isValidFormat(string $accessKey): bool {
        return preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}$/', $accessKey) === 1;
    }

    private function hashKey(string $accessKey): string {
        return hash('sha256', $accessKey);
    }

    private function assertValidRole(string $role): void {
        if (!in_array($role, self::ROLES, true)) {
            throw new \InvalidArgumentException('Invalid access key role.');
        }
    }
}

?>