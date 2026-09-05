<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AssignmentRepository;
use App\Validation\AssignmentValidator;
use InvalidArgumentException;
use RuntimeException;

class AssignmentService {
    public function __construct(private AssignmentRepository $assignmentRepository, private EmergencyService $emergencyService) {}

    public function confirm(int $driverId, int $orderId, string $assignmentControl): array {
        if ($driverId < 1 || $orderId < 1) {
            throw new InvalidArgumentException('Invalid assignment request.');
        }

        $assignmentControl = trim($assignmentControl);
        if ($assignmentControl === '') {
            throw new InvalidArgumentException('Assignment control is required.');
        }

        $this->emergencyService->assertNoActiveEmergency($driverId);

        $assignment = $this->assignmentRepository->findByIdentity($orderId, $driverId, $assignmentControl);
        if ($assignment === null) {
            throw new InvalidArgumentException('Assignment could not be found.');
        }

        if (!AssignmentValidator::canConfirm($assignment)) {
            throw new InvalidArgumentException('This assignment cannot be confirmed.');
        }

        $confirmed = $this->assignmentRepository->confirmAssignment($orderId, $driverId);

        if (!$confirmed) {
            throw new RuntimeException('Assignment could not be confirmed.');
        }

        return [
            'status' => 'success',
            'message' => 'Assignment confirmed successfully.'
        ];
    }

    public function cancel(int $driverId, int $orderId, string $assignmentControl, ?string $reason = null): array {
        if ($driverId < 1 || $orderId < 1) {
            throw new InvalidArgumentException('Invalid assignment request.');
        }

        $assignmentControl = trim($assignmentControl);
        if ($assignmentControl === '') {
            throw new InvalidArgumentException('Assignment control is required.');
        }

        $this->emergencyService->assertNoActiveEmergency($driverId);

        $assignment = $this->assignmentRepository->findByIdentity($orderId, $driverId, $assignmentControl);
        if ($assignment === null) {
            throw new InvalidArgumentException('Assignment could not be found.');
        }

        if (!AssignmentValidator::canCancel($assignment)) {
            throw new InvalidArgumentException('This assignment cannot be canceled.');
        }

        $canceled = $this->assignmentRepository->cancelAssignment($assignmentControl, $orderId, $driverId, $reason);
        if (!$canceled) {
            throw new RuntimeException('Assignment could not be canceled.');
        }

        return [
            'status' => 'success',
            'message' => 'Assignment successfully canceled.',
            'data' => [
                'assignment_control' => $assignmentControl,
                'order_id' => $orderId,
                'driver_id' => $driverId,
                'assignment_status' => 'canceled'
            ]
        ];
    }
}

?>