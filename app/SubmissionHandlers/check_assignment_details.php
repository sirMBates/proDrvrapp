<?php

use Core\Flash;
use Core\Storage;
use Core\Logger;
use App\Validation\Validator;
use App\Validation\AssignmentValidator;
use App\Sanitization\Sanitizer;

class UpdateAssignmentDetailsContr extends UpdateAssignment {
    private $assignmentControl;
    private $orderId;
    private $driverId;
    private $vehicleId;
    private $actualDropTime;
    private $actualEndTime;
    private $totalShiftTime;
    private $totalDriveTime;
    private $pickupDetails;
    private $destinationDetails;
    private $sharedJobNote;
    private $preSignature;
    private $postSignature;
    private Storage $storage;

    public function __construct(array $data, Storage $storage) {
        $this->assignmentControl = $data['assignment_control'] ?? null;
        $this->orderId = $data['order_id'] ?? null;
        $this->driverId = $data['driver_id'] ?? null;
        $this->vehicleId = $data['vehicle_id'] ?? null;
        $this->actualDropTime = $data['actual_drop_time'] ?? null;
        $this->actualEndTime = $data['actual_end_time'] ?? null;
        $this->totalShiftTime = $data['total_hrs'] ?? null;
        $this->totalDriveTime = isset($data['driving_time']) && trim((string) $data['driving_time']) !== '' ? $data['driving_time'] : '0.00';
        $this->pickupDetails = Sanitizer::plainText($data['pickup_details'] ?? '');
        $this->destinationDetails = Sanitizer::plainText($data['destination_details'] ?? '');
        $this->sharedJobNote = Sanitizer::plainText($data['shared_job_note'] ?? '');
        $this->preSignature = $data['pre_signature_base64'] ?? null;
        $this->postSignature = $data['post_signature_base64'] ?? null;
        $this->storage = $storage;
    }

    public function modify(): array {
        $alert = new Flash();
        $devLogger = new Logger('D:/webapps/logs/error.log');

        if ($this->isMissingInfo()) {
            $alert::setMsg('error', 'The assignment request is missing required information.');
            header("Location: /assignments?error=incomplete");
            exit();
        }

        if (!$this->validateAssignmentControl()) {
            $alert::setMsg('error', 'System error! Please contact dispatch.');
            header("Location: /assignments?error=system_error");
            exit();
        }

        if (!$this->validateAssignment()) {
            $alert::setMsg('error', 'Please check your assignment id.');
            header("Location: /assignments?error=assignment+id+failed");
            exit();
        }

        if (!$this->validateDriverId()) {
            $alert::setMsg('error', 'The driver information is invalid.');
            header("Location: /assignments?error=invalid+driver");
            exit();
        }

        $assignment = $this->getAssignmentByIdentity((string) $this->assignmentControl, (int) $this->orderId, (int) $this->driverId);
        if (!$assignment) {
            $alert::setMsg('error', 'The assignment could not be found.');
            header("Location: /assignments?error=missing_assignment");
            exit();
        }

        $signatureRequired = AssignmentValidator::requiresSignature($assignment);

        if (!AssignmentValidator::canSave($assignment)) {
            $alert::setMsg('error', 'Assignment changes are not available until two (2) hours before the scheduled start time.');
            header("Location: /assignments?error=currently+not+permitted");
            exit();
        }

        if (!Validator::optionalTime($this->actualDropTime)) {
            $alert::setMsg('error', 'The actual drop time is invalid.');
            header("Location: /assignments?error=invalid_drop_time&order_id=". urlencode((string) $this->orderId));
            exit();
        }

        if (!Validator::optionalDateTime($this->actualEndTime)) {
            $alert::setMsg('error', 'The actual end time is invalid.');
            header("Location: /assignments?error=invalid_end_time&order_id=". urlencode((string) $this->orderId));
            exit();
        }

        if (!Validator::optionalDecimalPlaces($this->totalShiftTime, 2)) {
            $alert::setMsg('error', 'Total job time is invalid.');
            header("Location: /assignments?error=invalid+total+hours&order_id=" . urlencode((string) $this->orderId));
            exit();
        }

        if (!Validator::optionalDecimalPlaces($this->totalDriveTime, 2)) {
            $alert::setMsg('error', 'Driving time is invalid.');
            header("Location: /assignments?error=invalid+total+hours&order_id=" . urlencode((string) $this->orderId));
            exit();
        }

        if (!$this->checkVehicleId()) {
            $alert::setMsg('warning', 'Please check your vehicle number and try again.');
            header("Location: /assignments?warning=incorrect+vehicle+id&order_id=". urlencode((string) $this->orderId));
            exit();
        }

        if (!Validator::optionalTextLength($this->pickupDetails)) {
            $alert::setMsg('warning', 'Pickup details exceed the allowed length.');
            header("Location: /assignments?warning=text_too_long&order_id=". urlencode((string) $this->orderId));
            exit();
        }

        if (!Validator::optionalTextLength($this->destinationDetails)) {
            $alert::setMsg('warning', 'Destination details exceed the allowed length.');
            header("Location: /assignments?warning=text_too_long&order_id=". urlencode((string) $this->orderId));
            exit();
        }

        if (!Validator::optionalTextLength($this->sharedJobNote)) {
            $alert::setMsg('warning', 'The shared job note exceeds the allowed length.');
            header("Location: /assignments?warning=text_too_long&order_id=". urlencode((string) $this->orderId));
            exit();
        }

        if (!Validator::optionalPngDataUrl($this->preSignature)) {
            $alert::setMsg('error', 'The pre-trip signature data is invalid.');
            header("Location: /assignments?error=invalid+signature&order_id=" . urlencode((string) $this->orderId));
            exit();
        }

        if (!Validator::optionalPngDataUrl($this->postSignature)) {
            $alert::setMsg('error', 'The post-trip signature data is invalid.');
            header("Location: /assignments?error=invalid+signature&order_id=" . urlencode((string) $this->orderId));
            exit();
        }

        $updateData = [
            'assignment_control' => $this->assignmentControl,
            'order_id' => $this->orderId,
            'driver_id' => $this->driverId,
            'vehicle_id' => $this->vehicleId,
            'actual_drop_time' => $this->actualDropTime,
            'actual_end_time' => $this->actualEndTime,
            'total_hrs' => $this->totalShiftTime,
            'driving_time' => $this->totalDriveTime,
            'pickup_details' => $this->pickupDetails,
            'destination_details' => $this->destinationDetails,
            'shared_job_note' => $this->sharedJobNote
        ];

        if ($signatureRequired) {
            $hasPreSignature = Validator::required($this->preSignature);
            $hasPostSignature = Validator::required($this->postSignature);

            if ($hasPreSignature || $hasPostSignature) {
                try {
                    $signatureData = $this->storage->saveSignatures([
                        'order_id' => $this->orderId,
                        'pre_signature_base64' => $this->preSignature,
                        'post_signature_base64' => $this->postSignature
                    ]);

                    $signatureData = array_filter($signatureData, static fn (mixed $value): bool => $value !== null);
                    $updateData = array_merge($updateData, $signatureData);
                } catch (\RuntimeException $exception) {
                    $devLogger->error('[SIGNATURE STORAGE ERROR] ' . $exception->getMessage());
                    $alert::setMsg('error', 'This signature could not be saved.');
                    header("Location: /assignments?error=signature+not+saved");
                    exit();
                }
            }
        } else {
            $updateData['signature_status'] = 'not-required';
        }

        return $this->modifyAssignment($updateData);
    }

    public function validateForCompletion(array $data, bool $verifyStoredSignatures = true): array {
        $alert = new Flash();

        // Basic required fields
        $requiredFields = [
            'assignment_control' => $this->assignmentControl,
            'order_id' => $this->orderId,
            'driver_id' => $this->driverId,
            'vehicle_id' => $this->vehicleId,
            'actual_drop_time' => $this->actualDropTime,
            'actual_end_time' => $this->actualEndTime,
            'total_hrs' => $this->totalShiftTime
        ];

        foreach ($requiredFields as $field => $value) {
            if (!Validator::required($value)) {
                $alert::setMsg('error', "Missing required field: $field");
                header("Location: /assignments?error=missing+" . urlencode($field));
                exit();
            }
        }

        if (!Validator::assignmentControl($this->assignmentControl)) {
            $alert::setMsg('error', 'System error! Please contact dispatch.');
            header("Location: /assignments?error=system_error");
            exit();
        }

        if (!Validator::positiveInteger($this->orderId)) {
            $alert::setMsg('error', 'Please check your assignment id.');
            header("Location: /assignments?error=assignment+id+failed");
            exit();
        }

        if (!Validator::positiveInteger($this->driverId)) {
            $alert::setMsg('error', 'The driver information is invalid.');
            header("Location: /assignments?error=invalid+driver");
            exit();
        }

        $assignment = $this->getAssignmentByIdentity((string) $this->assignmentControl, (int) $this->orderId, (int) $this->driverId);
        if (!$assignment) {
            $alert::setMsg('error', 'The assignment could not be found.');
            header("Location: /assignments?error=missing_assignment");
            exit();
        }

        if (!AssignmentValidator::canComplete($assignment)) {
            $alert::setMsg('error', 'This assignment cannot be completed before its scheduled start time.');
            header("Location: /assignments?error=completion+not+permitted");
            exit();
        }

        // Validate datetime
        if (!Validator::time($this->actualDropTime)) { 
            $alert::setMsg('error', 'Invalid drop time format.');
            header("Location: /assignments?error=invalid+drop+time&order_id=" . urlencode((string) $this->orderId));
            exit();
        }

        if (!Validator::dateTime($this->actualEndTime)) {
            $alert::setMsg('error', 'Invalid end time format.');
            header("Location: /assignments?error=invalid+end+time&order_id=" . urlencode((string) $this->orderId));
            exit();
        }

        // Validate decimal fields
        if (!Validator::decimalPlaces($this->totalShiftTime, 2)) { 
            $alert::setMsg('error', 'Invalid total hours.');
            header("Location: /assignments?error=invalid+total&order_id=" . urlencode((string) $this->orderId));
            exit();
        }

        if (!Validator::decimalPlaces($this->totalDriveTime ?? '0.00', 2)) { 
            $alert::setMsg('error', 'Invalid driving time.');
            header("Location: /assignments?error=invalid+drive+time&order_id=" . urlencode((string) $this->orderId));
            exit();
        }

        // Validate coach/vehicle number
        if (!Validator::minimumDigits($this->vehicleId, 3)) { 
            $alert::setMsg('warning', 'Please check your vehicle number and try again');
            header("Location: /assignments?warning=incorrect+vehicle+id&order_id=" . urlencode((string) $this->orderId));
            exit();
        }

        if (!AssignmentValidator::drivingTimeWithinTotal($this->totalDriveTime, $this->totalShiftTime)) {
            $alert::setMsg('warning', 'The driving time cannot be later than the total hours.');
            header("Location: /assignments?warning=driving+time+exceeded&order_id=" . urlencode((string) $this->orderId));
            exit();
        }

        if (!AssignmentValidator::dropTimeBeforeEnd($this->actualDropTime, $this->actualEndTime)) {
            $alert::setMsg('warning', 'The drop time cannot be later than the end time.');
            header("Location: /assignments?warning=drop+time+exceeded&order_id=" . urlencode((string) $this->orderId));
            exit();
        }

        $signatureRequired = AssignmentValidator::requiresSignature($assignment);
        if ($signatureRequired && $verifyStoredSignatures) {
            $this->verifySignaturesForCompletion($assignment);
        }

        $currentAssignment = $this->completeAssignment($data, false);

        return $currentAssignment;
    }

    public function verifySignaturesForCompletion(array $assignment): void {
        $alert = new Flash();
        $devLogger = new Logger('D:/webapps/logs/error.log');
        $signatureRequired = AssignmentValidator::requiresSignature($assignment);

        if (!$signatureRequired) {
            return;
        }

        try {
            $this->storage->verifySignatures($assignment);
        } catch (\RuntimeException $exception) {
            $devLogger->error('[ASSIGNMENT SIGNATURE CHECKER] ' . $exception->getMessage());
            $alert::setMsg('error', 'Both required signatures must be saved before completing this assignment.');
            header("Location: /assignments?error=missing+signature&order_id=" . urlencode((string) $assignment['order_id']));
            exit();
        }
    }

    private function isMissingInfo(): bool {
        $requiredFields = [
            $this->assignmentControl,
            $this->orderId,
            $this->driverId,
            $this->vehicleId
        ];

        foreach($requiredFields as $value) {
            if (!Validator::required($value)) {
                return true;
            }
        }
        return false;
    }

    private function validateAssignmentControl(): bool {
        return Validator::assignmentControl($this->assignmentControl);
    }

    private function validateAssignment(): bool {
        return Validator::positiveInteger($this->orderId);
    }

    private function validateDriverId(): bool {
        return Validator::positiveInteger($this->driverId);
    }

    private function checkVehicleId(): bool {
        return Validator::minimumDigits($this->vehicleId, 3);
    }
}

?>