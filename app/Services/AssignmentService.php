<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AssignmentRepository;
use App\Validation\AssignmentValidator;
use App\Repositories\DriverSharedNoteRepository;
use App\Validation\Validator;
use App\Sanitization\Sanitizer;
use InvalidArgumentException;
use RuntimeException;

class AssignmentService {
    public function __construct(private AssignmentRepository $assignmentRepository, private DriverSharedNoteRepository $driverSharedNoteRepository, private EmergencyService $emergencyService) {}

    public function confirm(int $orderId, int $driverId, string $assignmentControl): array {
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

    public function cancel(int $orderId, int $driverId, string $assignmentControl, ?string $reason = null): array {
        if ($orderId < 1 || $driverId < 1) {
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

        $canceled = $this->assignmentRepository->cancelAssignment($orderId, $driverId, $assignmentControl, $reason);
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

    public function prepareUpdate(int $orderId, int $driverId, string $assignmentControl, array $data): array {
        if ($driverId <= 0 || $orderId <= 0 || trim($assignmentControl) === '') {
            throw new \InvalidArgumentException('The assignment request is missing required information.');
        }

        $this->emergencyService->assertNoActiveEmergency($driverId);

        $assignment = $this->assignmentRepository->findByIdentity($orderId, $driverId, $assignmentControl);

        if ($assignment === null) {
            throw new \InvalidArgumentException('Assignment could not be found.');
        }

        if (!AssignmentValidator::canSave($assignment)) {
            throw new \InvalidArgumentException('Assignment changes are not available at this time.');
        }

        $vehicleId = trim((string) ($data['vehicle_id'] ?? ''));

        if (!Validator::minimumDigits($vehicleId, 3)) {
            throw new \InvalidArgumentException('Please check your vehicle number and try again.');
        }

        $actualDropTimeRaw = trim((string) ($data['actual_drop_time'] ?? ''));

        if (!Validator::optionalTime($actualDropTimeRaw)) {
            throw new \InvalidArgumentException('The actual drop time is invalid.');
        }

        $actualDropTime = $actualDropTimeRaw === '' ? null : $actualDropTimeRaw;

        $actualEndTimeRaw = trim((string) ($data['actual_end_time'] ?? ''));

        if (!Validator::optionalDateTime($actualEndTimeRaw)) {
            throw new \InvalidArgumentException('The actual end time is invalid.');
        }

        $actualEndTime = null;
        if ($actualEndTimeRaw !== '') {
            $normalizedEndTime = str_replace('T', ' ', $actualEndTimeRaw);

            $actualEndTime = strlen($normalizedEndTime) === 16 ? $normalizedEndTime . ':00' : $normalizedEndTime;
        }

        $totalJobTimeRaw = trim((string) ($data['total_hrs'] ?? ''));
        if (!Validator::optionalDecimalPlaces($totalJobTimeRaw, 2)) {
            throw new \InvalidArgumentException('Total job time is invalid.');
        }
        $totalJobTime = $totalJobTimeRaw === '' ? null : $totalJobTimeRaw;

        $drivingTime = trim((string) ($data['driving_time'] ?? ''));
        if ($drivingTime === '') {
            $drivingTime = '0.00';
        }

        if (!Validator::optionalDecimalPlaces($drivingTime, 2)) {
            throw new \InvalidArgumentException('Driving time is invalid.');
        }

        $pickupDetails = trim(Sanitizer::plainText($data['pickup_details'] ?? ''));
        $destinationDetails = trim(Sanitizer::plainText($data['destination_details'] ?? ''));
        $sharedJobNote = trim(Sanitizer::plainText($data['shared_job_note'] ?? ''));

        if (!Validator::optionalTextLength($pickupDetails)) {
            throw new \InvalidArgumentException('Pickup details exceed the allowed length.');
        }

        if (!Validator::optionalTextLength($destinationDetails)) {
            throw new \InvalidArgumentException('Destination details exceed the allowed length.');
        }

        if (!Validator::optionalTextLength($sharedJobNote)) {
            throw new \InvalidArgumentException('The shared job note exceeds the allowed length.');
        }

        return [
            'assignment' => $assignment,
            'data' => [
                'vehicle_id' => $vehicleId,
                'actual_drop_time' => $actualDropTime,
                'actual_end_time' => $actualEndTime,
                'total_job_time' => $totalJobTime,
                'driving_time' => $drivingTime,
                'pickup_details' => $pickupDetails,
                'destination_details' => $destinationDetails,
                'shared_job_note' => $sharedJobNote
            ]
        ];
    }

    public function updatePrepared(int $orderId, int $driverId, string $assignmentControl, array $preparedData): array {
        $this->emergencyService->assertNoActiveEmergency($driverId);

        $assignment = $this->assignmentRepository->findByIdentity($orderId, $driverId, $assignmentControl);
        if ($assignment === null) {
            throw new \InvalidArgumentException('Assignment could not be found.');
        }

        if (!AssignmentValidator::canSave($assignment)) {
            throw new \InvalidArgumentException('Assignment changes are not available at this time.');
        }

        $sharedJobNote = trim((string) ($preparedData['shared_job_note'] ?? ''));
        unset($preparedData['shared_job_note']);

        $updated = $this->assignmentRepository->updateAssignment($orderId, $driverId, $assignmentControl, $preparedData);
        if (!$updated) {
            throw new \RuntimeException('Assignment could not be updated.');
        }

        if ($sharedJobNote !== '') {
            $this->driverSharedNoteRepository->saveForAssignment($orderId, $driverId, $assignmentControl, $sharedJobNote);
        }

        return [
            'status' => 'success',
            'message' => 'Assignment updated successfully.',
            'data' => [
                'assignment_control' => $assignmentControl,
                'order_id' => $orderId,
                'order_ref' => (string) ($assignment['order_ref'] ?? ''),
                'driver_id' => $driverId
            ]
        ];
    }
}

?>