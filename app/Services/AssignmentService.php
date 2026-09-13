<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AssignmentRepository;
use App\Repositories\DriverSharedNoteRepository;
use App\Repositories\TimesheetRepository;
use App\Validation\AssignmentValidator;
use App\Validation\Validator;
use App\Sanitization\Sanitizer;
use App\ImportExport\AssignmentExporter;
use Core\Storage;
use InvalidArgumentException;
use RuntimeException;

class AssignmentService {
    public function __construct(private AssignmentRepository $assignmentRepository, private DriverSharedNoteRepository $driverSharedNoteRepository, private EmergencyService $emergencyService, private TimesheetRepository $timesheetRepository) {}

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

        $sharedJobNote = trim(Sanitizer::plainText($data['shared_job_note'] ?? ''));
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
                'shared_job_note' => $sharedJobNote
            ]
        ];
    }

    public function prepareCompletion(int $orderId, int $driverId, string $assignmentControl, array $data): array {
        if ($driverId <= 0 || $orderId <= 0 || trim($assignmentControl) === '') {
            throw new InvalidArgumentException('The assignment request is missing required information.');
        }

        $assignmentControl = trim($assignmentControl);
        $this->emergencyService->assertNoActiveEmergency($driverId);
        $assignment = $this->assignmentRepository->findFullByIdentity($orderId, $driverId, $assignmentControl);
        if ($assignment === null) {
            throw new InvalidArgumentException('Assignment could not be found.');
        }

        if (!AssignmentValidator::canComplete($assignment)) {
            throw new InvalidArgumentException('This assignment cannot be completed at this time.');
        }

        // Vehicle
        $vehicleId = trim((string) ($data['vehicle_id'] ?? ''));
        if (!Validator::minimumDigits($vehicleId, 3)) {
            throw new InvalidArgumentException('Please check your vehicle number and try again.');
        }

        // Actual drop time
        $actualDropTimeRaw = trim((string) ($data['actual_drop_time'] ?? ''));
        if ($actualDropTimeRaw === '' || !Validator::time($actualDropTimeRaw)) {
            throw new InvalidArgumentException('The actual drop time is required and must be valid.');
        }

        $actualDropTime = strlen($actualDropTimeRaw) === 5 ? $actualDropTimeRaw . ':00' : $actualDropTimeRaw;

        // Actual end time
        $actualEndTimeRaw = trim((string) ($data['actual_end_time'] ?? ''));
        if ($actualEndTimeRaw === '' || !Validator::dateTime($actualEndTimeRaw)) {
            throw new InvalidArgumentException('The actual end time is required and must be valid.');
        }

        $actualEndTime = str_replace('T', ' ', $actualEndTimeRaw);
        if (strlen($actualEndTime) === 16) {
            $actualEndTime .= ':00';
        }

        // Total job time
        $totalJobTimeRaw = trim((string) ($data['total_hrs'] ?? ''));
        if ($totalJobTimeRaw === '' || !Validator::decimalPlaces($totalJobTimeRaw, 2)) {
            throw new InvalidArgumentException('Total job time is required and must be valid.');
        }

        $totalJobTime = number_format((float) $totalJobTimeRaw, 2, '.', '');

        // Driving time
        $drivingTimeRaw = trim((string) ($data['driving_time'] ?? ''));
        if ($drivingTimeRaw === '') {
            $drivingTimeRaw = '0.00';
        }

        if (!Validator::decimalPlaces($drivingTimeRaw, 2)) {
            throw new InvalidArgumentException('Driving time is invalid.');
        }
        $drivingTime = number_format((float) $drivingTimeRaw, 2, '.', '');

        // Text fields - Shared Note only
        $sharedJobNote = trim(Sanitizer::plainText($data['shared_job_note'] ?? ''));
        if (!Validator::optionalTextLength($sharedJobNote)) {
            throw new InvalidArgumentException('The shared job note exceeds the allowed length.');
        }

        // This is the normalized state submitted by the driver.
        $submitted = [
            'vehicle_id' => $vehicleId,
            'actual_drop_time' => $actualDropTime,
            'actual_end_time' => $actualEndTime,
            'total_job_time' => $totalJobTime,
            'driving_time' => $drivingTime
        ];

        // Determine what ACTUALLY differs from the authoritative DB state.
        $changes = [];

        foreach ($submitted as $field => $value) {
            $storedValue = $this->normalizeCompletionValue($field, $assignment[$field] ?? null);
            $submittedValue = $this->normalizeCompletionValue($field, $value);

            if ($submittedValue !== $storedValue) {
                $changes[$field] = $value;
            }
        }

        // Build the assignment exactly as it would look after
        // applying the driver's unsaved changes.
        $finalAssignment = array_replace($assignment, $changes);

        // Validate rules that depend on the FINAL resulting state.
        if (!AssignmentValidator::drivingTimeWithinTotal($finalAssignment['driving_time'], $finalAssignment['total_job_time'])) {
            throw new InvalidArgumentException('Driving time cannot exceed total job time.');
        }

        if (!AssignmentValidator::dropTimeBeforeEnd($finalAssignment['actual_drop_time'], $finalAssignment['actual_end_time'])) {
            throw new InvalidArgumentException('The actual drop time must occur before the actual end time.');
        }

        /*
        * Signatures are intentionally NOT persisted here.
        * Complete may receive a newly captured signature that has not yet
        * been saved. The execution step will persist it first and then
        * verify the resulting authoritative signature state.
        */
        $signaturePayload = [
            'pre_signature_base64' => trim((string) ($data['pre_signature_base64'] ?? '')),
            'post_signature_base64' => trim((string) ($data['post_signature_base64'] ?? ''))
        ];

        return [
            'assignment' => $assignment,
            'changes' => $changes,
            'final_assignment' => $finalAssignment,
            'shared_job_note' => $sharedJobNote,
            'signature_payload' => $signaturePayload
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

    public function completePrepared(int $orderId, int $driverId, string $assignmentControl, array $prepared, Storage $storage, AssignmentExporter $exporter): array {
        if ($orderId < 1 || $driverId < 1 || trim($assignmentControl) === '') {
            throw new InvalidArgumentException('Invalid assignment completion request.');
        }

        $assignmentControl = trim($assignmentControl);

        // Recheck Emergency immediately before beginning completion work.
        $this->emergencyService->assertNoActiveEmergency($driverId);

        // Re-fetch the current authoritative assignment.
        // prepareCompletion() already checked eligibility, but time may have
        // passed between preparation and execution.
        $assignment = $this->assignmentRepository->findFullByIdentity($orderId, $driverId, $assignmentControl);

        if ($assignment === null) {
            throw new InvalidArgumentException('Assignment could not be found.');
        }

        if (!AssignmentValidator::canComplete($assignment)) {
            throw new InvalidArgumentException('This assignment cannot be completed at this time.');
        }

        $changes = $prepared['changes'] ?? [];
        if (!is_array($changes)) {
            throw new RuntimeException('Prepared assignment changes are invalid.');
        }

        $sharedJobNote = trim((string) ($prepared['shared_job_note'] ?? ''));

        $signaturePayload = $prepared['signature_payload'] ?? [];
        if (!is_array($signaturePayload)) {
            throw new RuntimeException('Prepared signature data is invalid.');
        }

        $preSignature = trim((string) ($signaturePayload['pre_signature_base64'] ?? ''));
        $postSignature = trim((string) ($signaturePayload['post_signature_base64'] ?? ''));
        $signatureRequired = AssignmentValidator::requiresSignature($assignment);

        // Validate submitted signature payloads before writing files.
        if ($preSignature !== '') {
            if (!AssignmentValidator::hasPreTripSignature(['pre_signature_base64' => $preSignature])) {
                throw new InvalidArgumentException('The pre-trip signature is invalid.');
            }
        }

        if ($postSignature !== '') {
            if (!AssignmentValidator::hasPostTripSignature(['post_signature_base64' => $postSignature])) {
                throw new InvalidArgumentException('The post-trip signature is invalid.');
            }
        }

        /*
        * Save newly submitted signatures.
        * Storage ignores empty signature values, so an existing pre-trip
        * signature is left untouched when only a new post-trip signature
        * is submitted during completion.
        */
        if ($signatureRequired) {
            if ($preSignature !== '' || $postSignature !== '') {
                $signatureData = $storage->saveSignatures([
                    'order_id' => $orderId,
                    'pre_signature_base64' => $preSignature,
                    'post_signature_base64' => $postSignature
                ]);

                foreach ($signatureData as $field => $value) {
                    // Do not overwrite existing database metadata with NULL
                    // for a signature that was not submitted.
                    if ($value !== null) {
                        $changes[$field] = $value;
                    }
                }
            }
        } else {
            // Keep non-signature assignments explicit in the database.
            if (($assignment['signature_status'] ?? null) !== 'not-required') {
                $changes['signature_status'] = 'not-required';
            }
        }

        // Persist only fields that actually changed plus any newly generated
        // signature metadata.
        $updated = $this->assignmentRepository->updateAssignmentChanges($orderId, $driverId, $assignmentControl, $changes);
        if (!$updated) {
            throw new RuntimeException('Assignment completion changes could not be saved.');
        }

        // Shared notes participate in the same outer database transaction.
        if ($sharedJobNote !== '') {
            $this->driverSharedNoteRepository->saveForAssignment($orderId, $driverId, $assignmentControl, $sharedJobNote);
        }

        // Everything from this point forward uses the authoritative database
        // representation — never the original POST data.
        $finalAssignment = $this->assignmentRepository->findFullByIdentity($orderId, $driverId, $assignmentControl);
        if ($finalAssignment === null) {
            throw new RuntimeException('Updated assignment could not be reloaded.');
        }

        // Required signatures must now exist on disk and match the database
        // metadata/hashes.
        $storage->verifySignatures($finalAssignment);

        // Export the authoritative final assignment.
        
        // Passing an empty submitted-data array prevents the existing exporter
        // from preferring raw POST values over the database values.
        $exported = $exporter->assignmentSubmitted([], $finalAssignment);
        if (!$exported) {
            throw new RuntimeException('Assignment could not be submitted to dispatch.');
        }

        // Emergency is checked again immediately before the irreversible
        // business-state transition.
        $this->emergencyService->assertNoActiveEmergency($driverId);

        // confirmed → completed

        // markCompleted() itself has guarded SQL requiring the assignment
        // to still be confirmed, uncanceled, and incomplete.
        $this->assignmentRepository->markCompleted($orderId, $driverId, $assignmentControl);

        // Reload again because completed_at is generated by MySQL.
        $completedAssignment = $this->assignmentRepository->findFullByIdentity($orderId, $driverId, $assignmentControl);
        if ($completedAssignment === null) {
            throw new RuntimeException('Completed assignment could not be reloaded.');
        }

        if (($completedAssignment['assignment_status'] ?? '') !== 'completed' || empty($completedAssignment['completed_at'])) {
            throw new RuntimeException('Assignment completion could not be verified.');
        }

        // One completed assignment = one authoritative Timesheet snapshot.
        $timesheetCreated = $this->timesheetRepository->createCompletionSnapshot($completedAssignment);
        if (!$timesheetCreated) {
            throw new RuntimeException('The Timesheet entry could not be created.');
        }

        return [
            'status' => 'success',
            'message' => 'Assignment completed and submitted.',
            'data' => [
                'assignment_control' => $assignmentControl,
                'order_id' => $orderId,
                'order_ref' => (string) ($completedAssignment['order_ref'] ?? ''),
                'driver_id' => $driverId,
                'assignment_status' => 'completed',
                'completed_at' => ($completedAssignment['completed_at'] ?? null)
            ]
        ];
    }

    private function normalizeCompletionValue(string $field, mixed $value): string {
        if ($value === null) {
            return '';
        }

        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        return match ($field) {
            'total_job_time',
            'driving_time' => number_format((float) $value, 2, '.', ''),
            'actual_drop_time' => strlen($value) === 5 ? $value . ':00' : $value,
            'actual_end_time' => $this->normalizeCompletionDateTime($value),
            default => $value
        };
    }

    private function normalizeCompletionDateTime(string $value): string {
        $value = str_replace('T', ' ', trim($value));
        return strlen($value) === 16 ? $value . ':00' : $value;
    }
}

?>