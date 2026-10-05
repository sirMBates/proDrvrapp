<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AssignmentRepository;
use App\Validation\AssignmentValidator;
use Core\Storage;
use PDO;
use InvalidArgumentException;

final class SignatureService {
    public function __construct(private PDO $pdo, private AssignmentRepository $assignmentRepository, private AssignmentStatusVerificationService $assignmentStatusVerificationService, private Storage $storage) {}

    public function saveSignature(array $signatureRequest, string $signature): array {
        $orderId = (int) ($signatureRequest['order_id'] ?? 0);
        $driverId = (int) ($signatureRequest['driver_id'] ?? 0);
        $assignmentControl = trim((string) ($signatureRequest['assignment_control'] ?? ''));

        if ($orderId < 1 || $driverId < 1 || $assignmentControl === '') {
            throw new InvalidArgumentException('Invalid signature request.');
        }

        $assignment = $this->assignmentRepository->findByIdentity($orderId, $driverId, $assignmentControl);
        if ($assignment === null) {
            throw new InvalidArgumentException('Assignment could not be found.');
        }

        if (!AssignmentValidator::requiresSignature($assignment)) {
            throw new InvalidArgumentException('This assignment does not require a signature.');
        }

        $signatureBackup = null;
        $signatureData = [];

        try {
            if (!$this->pdo->beginTransaction()) {
                throw new \RuntimeException('Unable to begin signature transaction.');
            }

            /*
            * No signature validation, file processing, or signature metadata updates occur before assignment status verification.
            */
            $this->assignmentStatusVerificationService->verifyOrFail($assignment, $driverId, trim((string) ($assignment['vehicle_id'] ?? '')));

            $signatureType = trim((string) ($signatureRequest['signature_type'] ?? ''));

            if (!in_array($signatureType, ['pre', 'post'], true)) {
                throw new InvalidArgumentException('Invalid signature type.');
            }

            if ($signatureType === 'pre') {
                $validSignature = AssignmentValidator::hasPreTripSignature(['pre_signature_base64' => $signature]);
            } else {
                $validSignature = AssignmentValidator::hasPostTripSignature(['post_signature_base64' => $signature]);
            }

            if (!$validSignature) {
                throw new InvalidArgumentException('The submitted signature is invalid.');
            }

            $signaturePayload = [
                'order_id' => $orderId,
                'pre_signature_base64' => '',
                'post_signature_base64' => ''
            ];

            if ($signatureType === 'pre') {
                $signaturePayload['pre_signature_base64'] = $signature;
            } else {
                $signaturePayload['post_signature_base64'] = $signature;
            }

            $signatureBackup = $this->storage->createSignatureBackup($orderId);
            $signatureData = $this->storage->saveSignatures($signaturePayload);

            if ($signatureType === 'pre') {
                $signatureChanges = [
                    'pre_signature_path' => $signatureData['pre_signature_path'],
                    'pre_signature_hash' => $signatureData['pre_signature_hash'],
                    'pre_signature_at' => $signatureData['pre_signature_at'],
                    'signature_status' => $signatureData['signature_status']
                ];
            } else {
                $signatureChanges = [
                    'post_signature_path' => $signatureData['post_signature_path'],
                    'post_signature_hash' => $signatureData['post_signature_hash'],
                    'post_signature_at' => $signatureData['post_signature_at'],
                    'signature_status' => $signatureData['signature_status']
                ];
            }

            $updated = $this->assignmentRepository->updateSignatureChanges($orderId, $driverId, $assignmentControl, $signatureChanges);

            if (!$updated) {
                throw new \RuntimeException('Signature metadata could not be saved.');
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            if ($signatureBackup !== null) {
                $this->storage->rollbackSignatureBackup($signatureBackup);
            }

            throw $e;
        }

        if ($signatureBackup !== null) {
            try {
                $this->storage->discardSignatureBackup($signatureBackup);
            } catch (\Throwable $cleanupError) {
                error_log('[SIGNATURE BACKUP CLEANUP] ' . $cleanupError->getMessage());
            }
        }

        return $signatureData;
    }

    public function reusePreSignatureAsPost(int $orderId, int $driverId, string $assignmentControl): array {
        if ($orderId < 1 || $driverId < 1 || trim($assignmentControl) === '') {
            throw new InvalidArgumentException('Invalid signature request.');
        }

        $assignmentControl = trim($assignmentControl);
        $assignment = $this->assignmentRepository->findByIdentity($orderId, $driverId, $assignmentControl);
        if ($assignment === null) {
            throw new InvalidArgumentException('Assignment could not be found.');
        }

        if (!AssignmentValidator::requiresSignature($assignment)) {
            throw new InvalidArgumentException('This assignment does not require a signature.');
        }

        $backup = null;
        $signatureData = [];

        try {
            if (!$this->pdo->beginTransaction()) {
                throw new \RuntimeException('Unable to begin signature transaction.');
            }

            $this->assignmentStatusVerificationService->verifyOrFail($assignment, $driverId, trim((string) ($assignment['vehicle_id'] ?? '')));

            $preSignaturePath = $assignment['pre_signature_path'] ?? null;

            if (!is_string($preSignaturePath) || $preSignaturePath === '') {
                throw new InvalidArgumentException('A pre-inspection signature is not available.');
            }

            $backup = $this->storage->createSignatureBackup($orderId, $assignmentControl);
            $signatureData = $this->storage->reusePreSignatureAsPost($preSignaturePath);

            $updated = $this->assignmentRepository->updateSignatureChanges($orderId, $driverId, $assignmentControl, $signatureData);

            if (!$updated) {
                throw new \RuntimeException('Post-signature metadata could not be saved.');
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            if ($backup !== null) {
                try {
                    $this->storage->rollbackSignatureBackup($backup);
                } catch (\Throwable $rollbackError) {
                    error_log('[SIGNATURE FILE ROLLBACK ERROR] ' . $rollbackError->getMessage());
                }
            }

            throw $e;
        }

        if ($backup !== null) {
            try {
                $this->storage->discardSignatureBackup($backup);
            } catch (\Throwable $cleanupError) {
                error_log('[SIGNATURE BACKUP CLEANUP ERROR] ' . $cleanupError->getMessage());
            }
        }

        return $signatureData;
    }
}

?>