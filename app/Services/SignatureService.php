<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AssignmentRepository;
use App\Validation\AssignmentValidator;
use Core\Storage;
use InvalidArgumentException;

final class SignatureService {
    public function __construct(private AssignmentRepository $assignmentRepository, private Storage $storage) {}

    public function saveSignature(array $signatureRequest, string $signature): array {
        $orderId = (int) ($signatureRequest['order_id'] ?? 0);
        $driverId = (int) ($signatureRequest['driver_id'] ?? 0);
        $assignmentControl = trim((string) ($signatureRequest['assignment_control'] ?? ''));

        $assignment = $this->assignmentRepository->findByIdentity($orderId, $driverId, $assignmentControl);
        if ($assignment === null) {
            throw new \RuntimeException('Assignment could not be found.');
        }

        if (!AssignmentValidator::requiresSignature($assignment)) {
            throw new \RuntimeException('This assignment does not require a signature.');
        }

        $signatureType = trim((string) ($signatureRequest['signature_type'] ?? ''));
        if (!in_array($signatureType, ['pre', 'post'], true)) {
            throw new \RuntimeException('Invalid signature type.');
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

        try {
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

            $this->storage->discardSignatureBackup($signatureBackup);

            return $signatureData;
        } catch (\Throwable $e) {
            $this->storage->rollbackSignatureBackup($signatureBackup);
            throw $e;
        }
    }

    public function reusePreSignatureAsPost(int $orderId, int $driverId, string $assignmentControl): array {
        $assignment = $this->assignmentRepository->findByIdentity($orderId, $driverId, $assignmentControl);
        if ($assignment === null) {
            throw new InvalidArgumentException('Assignment could not be found.');
        }

        if (!AssignmentValidator::requiresSignature($assignment)) {
            throw new InvalidArgumentException('This assignment does not require a signature.');
        }

        $preSignaturePath = $assignment['pre_signature_path'] ?? null;

        if (!is_string($preSignaturePath) || $preSignaturePath === '') {
            throw new InvalidArgumentException('A pre-inspection signature is not available.');
        }

        $backup = $this->storage->createSignatureBackup($orderId, $assignmentControl);

        try {
            $signatureData = $this->storage->reusePreSignatureAsPost($preSignaturePath);

            $this->assignmentRepository->updateSignatureChanges($orderId, $driverId, $assignmentControl, $signatureData);

            $this->storage->discardSignatureBackup($backup);

            return $signatureData;
        } catch (\Throwable $e) {
            $this->storage->rollbackSignatureBackup($backup);

            throw $e;
        }
    }
}


?>