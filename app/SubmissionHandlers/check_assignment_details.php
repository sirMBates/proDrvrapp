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