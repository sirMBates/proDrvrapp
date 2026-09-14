<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use RuntimeException;

class SignatureRequestRepository {
    public function __construct(private PDO $pdo) {}

    public function revokeActiveRequest(int $orderId, int $driverId, string $assignmentControl, string $signatureType): bool {
        $sql = "UPDATE signature_requests
                SET revoked_at = CURRENT_TIMESTAMP
                WHERE order_id = :order_id
                AND driver_id = :driver_id
                AND assignment_control = :assignment_control
                AND signature_type = :signature_type
                AND used_at IS NULL
                AND revoked_at IS NULL
                AND expires_at > CURRENT_TIMESTAMP";
        $stmt = $this->pdo->prepare($sql);

        $executed = $stmt->execute([
            ':order_id' => $orderId,
            ':driver_id' => $driverId,
            ':assignment_control' => $assignmentControl,
            ':signature_type' => $signatureType
        ]);

        if (!$executed) {
            throw new RuntimeException('Active signature request could not be revoked.');
        }

        return true;
    }

    public function createRequest(int $orderId, int $driverId, string $assignmentControl, string $signatureType, string $tokenHash, string $expiresAt): int {
        $sql = "INSERT INTO signature_requests (order_id, driver_id, assignment_control, signature_type, token_hash, expires_at) 
                VALUES (:order_id, :driver_id, :assignment_control, :signature_type, :token_hash, :expires_at)";
        $stmt = $this->pdo->prepare($sql);

        $executed = $stmt->execute([
            ':order_id' => $orderId,
            ':driver_id' => $driverId,
            ':assignment_control' => $assignmentControl,
            ':signature_type' => $signatureType,
            ':token_hash' => $tokenHash,
            ':expires_at' => $expiresAt
        ]);

        if (!$executed) {
            throw new RuntimeException('Signature request could not be created.');
        }

        $requestId = (int) $this->pdo->lastInsertId();
        if ($requestId <= 0) {
            throw new RuntimeException('Signature request ID could not be retrieved.');
        }

        return $requestId;
    }

    public function findByTokenHash(string $tokenHash): ?array {
        $sql = "SELECT * FROM signature_requests
                WHERE token_hash = :token_hash
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);

        $executed = $stmt->execute([
            ':token_hash' => $tokenHash
        ]);

        if (!$executed) {
            throw new RuntimeException('Signature request lookup failed.');
        }

        $request = $stmt->fetch();

        return $request !== false ? $request : null;
    }

    public function findActiveByTokenHash(string $tokenHash): ?array {
        $sql = "SELECT * FROM signature_requests
                WHERE token_hash = :token_hash
                AND used_at IS NULL
                AND revoked_at IS NULL
                AND expires_at > CURRENT_TIMESTAMP
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);

        $executed = $stmt->execute([
            ':token_hash' => $tokenHash
        ]);

        if (!$executed) {
            throw new RuntimeException('Active signature request lookup failed.');
        }

        $request = $stmt->fetch();

        return $request !== false ? $request : null;
    }

    public function markUsed(int $signatureRequestId): bool {
        $sql = "UPDATE signature_requests
                SET used_at = CURRENT_TIMESTAMP
                WHERE signature_request_id = :signature_request_id
                AND used_at IS NULL
                AND revoked_at IS NULL
                AND expires_at > CURRENT_TIMESTAMP";
        $stmt = $this->pdo->prepare($sql);

        $executed = $stmt->execute([
            ':signature_request_id' => $signatureRequestId
        ]);

        if (!$executed) {
            throw new RuntimeException('Signature request could not be marked as used.');
        }

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException('Signature request is no longer active.');
        }

        return true;
    }
}

?>