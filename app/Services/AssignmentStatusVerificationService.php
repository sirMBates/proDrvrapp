<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DriverStatus;
use App\Repositories\AssignmentRepository;
use App\Repositories\DriverStatusRepository;
use InvalidArgumentException;
use RuntimeException;

final class AssignmentStatusVerificationService {
    public function __construct(private AssignmentRepository $assignmentRepository, private DriverStatusRepository $driverStatusRepository) {}

    public function verifyOrFail(array $assignment, int $driverId, string $proposedVehicleId): array {
        $orderId = (int) ($assignment['order_id'] ?? 0);
        $assignmentDriverId = (int) ($assignment['driver_id'] ?? 0);

        $assignmentControl = trim((string) ($assignment['assignment_control'] ?? ''));
        $startDateTime = trim((string) ($assignment['start_date_time'] ?? ''));
        $proposedVehicleId = trim($proposedVehicleId);

        if ($orderId < 1 || $driverId < 1 || $assignmentDriverId !== $driverId || $assignmentControl === '' || $startDateTime === '' || $proposedVehicleId === '') {
            throw new InvalidArgumentException('Invalid assignment status verification request.');
        }

        if (($assignment['assignment_status'] ?? '') !== 'confirmed' || !empty($assignment['completed_at']) || !empty($assignment['canceled_at'])) {
            throw new InvalidArgumentException('This assignment is not available for status verification.');
        }

        if ($this->isVerified($assignment)) {
            return [
                'verified' => true,
                'alreadyVerified' => true,
                'verifiedAt' => $assignment['status_verified_at'],
                'verifiedVehicleId' => (string) ($assignment['status_verified_vehicle_id'] ?? ''),
                'arrivedLocationStatusId' => (int) ($assignment['arrived_location_status_id'] ?? 0),
                'onAssignmentStatusId' => (int) ($assignment['on_assignment_status_id'] ?? 0)
            ];
        }

        $verificationAnchor = $this->resolveAnchor($assignment, $driverId);
        $statusRecords = $this->driverStatusRepository->findAvailableAssignmentStatuses($driverId, $verificationAnchor);
        $statusPair = $this->findOrderedStatusPair($statusRecords);

        if ($statusPair['arrived'] === null) {
            throw new InvalidArgumentException('Select At Location, followed by On Assignment, ' . 'before continuing with this assignment.');
        }

        if ($statusPair['onAssignment'] === null) {
            throw new InvalidArgumentException('You have recorded At Location. ' . 'Select On Assignment to continue.');
        }

        $arrivedStatusId = (int) $statusPair['arrived']['status_id'];
        $onAssignmentStatusId = (int) $statusPair['onAssignment']['status_id'];

        $recorded = $this->assignmentRepository ->recordStatusVerification($orderId, $driverId, $assignmentControl, $proposedVehicleId, $arrivedStatusId, $onAssignmentStatusId);

        if (!$recorded) {
            throw new RuntimeException('Assignment status verification could not be recorded.');
        }

        return [
            'verified' => true,
            'alreadyVerified' => false,
            'verifiedAt' => null,
            'verifiedVehicleId' => $proposedVehicleId,
            'arrivedLocationStatusId' => $arrivedStatusId,
            'onAssignmentStatusId' => $onAssignmentStatusId
        ];
    }

    private function resolveAnchor(array $assignment, int $driverId): string {
        $orderId = (int) $assignment['order_id'];
        $startDateTime = (string) $assignment['start_date_time'];

        $previousAssignment = $this->assignmentRepository->findPreviousTerminalAssignment($driverId, $orderId, $startDateTime);
        if ($previousAssignment === null) {
            return $startDateTime;
        }

        $previousStatus = (string) ($previousAssignment['assignment_status'] ?? '');
        if ($previousStatus === 'completed') {
            $completedAt = trim((string) ($previousAssignment['completed_at'] ?? ''));

            if ($completedAt !== '') {
                return $completedAt;
            }
        }

        if ($previousStatus === 'canceled') {
            $canceledAt = trim((string) ($previousAssignment['canceled_at'] ?? ''));

            if ($canceledAt !== '') {
                return $canceledAt;
            }
        }

        return $startDateTime;
    }

    private function findOrderedStatusPair(array $statusRecords): array {
        $arrivedStatus = null;

        foreach ($statusRecords as $statusRecord) {
            $status = (string) ($statusRecord['status'] ?? '');

            if ($status === DriverStatus::ARRIVED_AT_LOCATION->value) {
                /*
                 * Retain the most recent available arrival
                 * before On Assignment.
                 */
                $arrivedStatus = $statusRecord;
                continue;
            }

            if ($status === DriverStatus::ON_ASSIGNMENT->value && $arrivedStatus !== null) {
                return [
                    'arrived' => $arrivedStatus,
                    'onAssignment' => $statusRecord
                ];
            }
        }

        return [
            'arrived' => $arrivedStatus,
            'onAssignment' => null
        ];
    }

    private function isVerified(array $assignment): bool {
        return
            !empty($assignment['status_verified_at']) && (int) ($assignment['arrived_location_status_id'] ?? 0) > 0 && (int) ($assignment['on_assignment_status_id'] ?? 0) > 0;
    }
}

?>