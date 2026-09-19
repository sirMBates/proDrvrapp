<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AssignmentRepository;
use App\Repositories\SignatureRequestRepository;
use DateTimeImmutable;
use PDO;
use RuntimeException;

class SignatureRequestService {
    private const TOKEN_LIFETIME_MINUTES = 5;

    private const ALLOWED_SIGNATURE_TYPES = [
        'pre',
        'post',
    ];

    public function __construct(private PDO $pdo, private SignatureRequestRepository $signatureRequestRepository, private AssignmentRepository $assignmentRepository) {}

    public function createRequest(int $orderId, int $driverId, string $assignmentControl, string $signatureType): array {
        $this->validateIdentity($orderId, $driverId, $assignmentControl);

        $this->validateSignatureType($signatureType);

        $assignment = $this->assignmentRepository->findByIdentity($orderId, $driverId, $assignmentControl);
        if ($assignment === null) {
            throw new \RuntimeException('Assignment could not be found.');
        }

        if ((int) ($assignment['signature_required'] ?? 0) !== 1) {
            throw new \RuntimeException('This assignment does not require a signature.');
        }

        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = (new DateTimeImmutable())->modify('+' . self::TOKEN_LIFETIME_MINUTES . ' minutes')->format('Y-m-d H:i:s');

        try {
            $this->pdo->beginTransaction();

            $this->signatureRequestRepository->revokeActiveRequest($orderId, $driverId, $assignmentControl, $signatureType);

            $signatureRequestId = $this->signatureRequestRepository->createRequest($orderId, $driverId, $assignmentControl, $signatureType, $tokenHash, $expiresAt);

            $this->pdo->commit();

            return [
                'signature_request_id' => $signatureRequestId,
                'token' => $rawToken,
                'signature_type' => $signatureType,
                'expires_at' => $expiresAt,
                'expires_in_seconds' => self::TOKEN_LIFETIME_MINUTES * 60,
            ];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function getRequestByToken(string $rawToken): ?array {
        $rawToken = trim($rawToken);

        if (!$this->isValidTokenFormat($rawToken)) {
            return null;
        }

        $tokenHash = hash('sha256', $rawToken);

        return $this->signatureRequestRepository->findByTokenHash($tokenHash);
    }

    public function getActiveRequestByToken(string $rawToken): ?array {
        $rawToken = trim($rawToken);

        if (!$this->isValidTokenFormat($rawToken)) {
            return null;
        }

        $tokenHash = hash('sha256', $rawToken);

        return $this->signatureRequestRepository->findActiveByTokenHash($tokenHash);
    }

    public function consumeRequest(int $signatureRequestId): void {
        if ($signatureRequestId <= 0) {
            throw new RuntimeException('Invalid signature request ID.');
        }

        $this->signatureRequestRepository->markUsed($signatureRequestId);
    }

    private function validateIdentity(int $orderId, int $driverId, string $assignmentControl): void {
        if ($orderId <= 0) {
            throw new RuntimeException('Invalid order ID.');
        }

        if ($driverId <= 0) {
            throw new RuntimeException('Invalid driver ID.');
        }

        if (trim($assignmentControl) === '') {
            throw new RuntimeException('Assignment control is required.');
        }
    }

    private function validateSignatureType(string $signatureType): void {
        if (!in_array($signatureType, self::ALLOWED_SIGNATURE_TYPES, true)) {
            throw new RuntimeException('Invalid signature type.');
        }
    }

    private function isValidTokenFormat(string $rawToken): bool {
        return strlen($rawToken) === 64 && ctype_xdigit($rawToken);
    }
}

?>