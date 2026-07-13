<?php

use core\Flash;

class UpdateAssignmentDetailsContr extends UpdateAssignment {
    private $driverId;
    private $orderId;
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
    private $signatureStatus;

    public function __construct(array $data) {
        $this->driverId = $data['driver_id'] ?? null;
        $this->orderId = $data['order_id'] ?? null;
        $this->vehicleId = $data['vehicle_id'] ?? null;
        $this->actualDropTime = $data['actual_drop_time'] ?? null;
        $this->actualEndTime = $data['actual_end_time'] ?? null;
        $this->totalShiftTime = $data['total_hrs'] ?? null;
        $this->totalDriveTime = $data['driving_time'] ?? null;
        $this->pickupDetails = $this->validateTextarea($data['pickup_details'] ?? '');
        $this->destinationDetails = $this->validateTextarea($data['destination_details'] ?? '');
        $this->sharedJobNote = $this->validateTextarea($data['shared_job_note'] ?? '');
        $this->preSignature = $data['pre_signature_base64'] ?? null;
        $this->postSignature = $data['post_signature_base64'] ?? null;
        $this->signatureStatus = $data['signature_status'] ?? null;
    }

    public function modify() {
        $alert = new Flash();

        $signatureRequired = (isset($_POST['signature_required']) && $_POST['signature_required'] === "1");

        if ($this->isMissingInfo($signatureRequired)) {
            $alert::setMsg('error', 'Please complete all fields before updating.');
            header("Location: /assignments?error=incomplete");
            exit();
        }

        if (!$this->validateSignature($this->preSignature)) {
            $alert::setMsg('error', 'Invalid pre-trip signature format.');
            header("Location: /assignments?error=invalid+signature");
            exit();
        }

        if (!$this->validateSignature($this->postSignature)) {
            $alert::setMsg('error', 'Invalid post-trip signature format.');
            header("Location: /assignments?error=invalid+signature");
            exit();
        }

        if (!$this->checkDatesAndTimes()) {
            $alert::setMsg('warning', 'Please check your dates or times and try again.');
            header("Location: /assignments?warning=incompatible+date+or+time");
            exit();
        }

        if (!$this->checkHoursOfService()) {
            $alert::setMsg('warning', 'Please check your total hours and try again.');
            header("Location: /assignments?warning=tot+hrs+incorrect");
            exit();
        }

        if (!$this->checkCoachId()) {
            $alert::setMsg('warning', 'Please check your vehicle number and try again.');
            header("Location: /assignments?warning=incorrect+coach+id");
            exit();
        }

        if (!$this->validateSignatureStatus()) {
            $alert::setMsg('error', 'Invalid signature status.');
            header("Location: /assignments?error=invalid+signature+state");
            exit();
        }

        return $this->modifyAssignment([
            'driver_id' => $this->driverId,
            'order_id' => $this->orderId,
            'vehicle_id' => $this->vehicleId,
            'actual_drop_time' => $this->actualDropTime,
            'actual_end_time' => $this->actualEndTime,
            'total_hrs' => $this->totalShiftTime,
            'driving_time' => $this->totalDriveTime,
            'pickup_details' => $this->pickupDetails,
            'destination_details' => $this->destinationDetails,
            'shared_job_note' => $this->sharedJobNote,
            'pre_signature_base64' => $this->preSignature,
            'post_signature_base64' => $this->postSignature,
            'signature_status' => $this->signatureStatus
        ]);
    }

    public function complete(array $data): array {
        $alert = new Flash();

        // Basic required fields
        $requiredFields = [
            'driver_id',
            'order_id',
            'vehicle_id',
            'actual_drop_time',
            'actual_end_time',
            'total_hrs',
            'driving_time'
        ];

        $errors = [];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = 'This field is required.';
            }
        }

        // Validate datetime
        if (!empty($data['actual_drop_time']) && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $data['actual_drop_time'])) {
            $errors['actual_drop_time'] = 
            $alert::setMsg('error,', 'Invalid drop time format.');
            header("Location: /assignments?error=invalid+drop+time");
            exit();
        }

        if (!empty($data['actual_end_time']) && !preg_match('/^\d{4}-\d{2}-\d{2}T([01]\d|2[0-3]):[0-5]\d$/', $data['actual_end_time'])) {
            $errors['actual_end_time'] = 
            $alert::setMsg('error', 'Invalid end time format.');
            header("Location: /assignments?error=invalid+end+time");
            exit();
        }

        // Validate decimal fields
        if (!empty($data['total_hrs']) && !preg_match('/^\d+(\.\d{1,2})?$/', $data['total_hrs'])) {
            $errors['total_hrs'] = 
            $alert::setMsg('error', 'Invalid total hours.');
            header("Location: /assignments?error=invalid+total");
            exit();
        }

        if (!empty($data['driving_time']) && !preg_match('/^\d+(\.\d{1,2})?$/', $data['driving_time'])) {
            $errors['driving_time'] = 
            $alert::setMsg('error', 'Invalid driving time.');
            header("Location: /assignments?error=invalid+drive+time");
            exit();
        }

        // Validate coach/vehicle number
        if (!empty($data['vehicle_id']) && !preg_match('/^\d{3,}$/', $data['vehicle_id'])) {
            $errors['vehicle_id'] = 
            $alert::setMsg('error', 'Invalid vehicle number.');
            header("Location: /assignments?error=invalid+vehicle");
            exit();
        }

        // Signature check
        if (isset($data['signature_status']) && !in_array($data['signature_status'], ['pending','pre-trip-complete','complete'], true)) {
            $errors['signature_status'] = 
            $alert::setMsg('error', 'Invalid signature status.');
            header("Location: /assignments?error=no+signature");
            exit();
        }

        // Sanitize shared job note
        if (!empty($data['shared_job_note'])) {
            $data['shared_job_note'] = $this->validateTextarea($data['shared_job_note']);
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'data' => $data
        ];
    }

    private function isMissingInfo(bool $signatureRequired = false): bool {
        $requiredFields = [
            $this->driverId,
            $this->orderId,
            $this->vehicleId,
            $this->actualDropTime,
            $this->actualEndTime,
            $this->totalShiftTime,
            $this->totalDriveTime
        ];

        foreach($requiredFields as $value) {
            if (empty($value)) {
                return true;
            }
        }

        if (!$signatureRequired) {
            return false;
        }

        if (empty($this->preSignature) && empty($this->postSignature)) {
            return true;
        }
        return false;
    }

    private function checkDatesAndTimes(): bool {
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $this->actualDropTime)) {
            return false;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}T([01]\d|2[0-3]):[0-5]\d$/', $this->actualEndTime)) {
            return false;
        }

        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $this->actualEndTime);
        if (!$dt) return false;

        return true;
    }

    private function checkHoursOfService(): bool {
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $this->totalShiftTime)) {
            return false;
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $this->totalDriveTime)) {
            return false;
        }
        return true;
    }

    private function checkCoachId(): bool {
        if (!preg_match('/^\d{3,}$/', $this->vehicleId)) {
            return false;
        }
        return true;
    }

    private function validateSignature(?string $sig): bool {
        if (empty($sig)) {
            return true;
        }

        $prefix = 'data:image/png;base64,';
        // Must begin with the correct data URI
        if (strpos($sig, $prefix) !== 0) {
            return false;
        }

        // Strip header
        $raw = substr($sig, strlen($prefix));

        // Must be valid base64
        $decoded = base64_decode($raw, true);
        if ($decoded === false) {
            return false;
        }

        // Check PNG header bytes
        if (substr($decoded, 0, 8) !== "\x89PNG\r\n\x1A\n") {
            return false;
        }

        // Max size (e.g., 1MB)
        if (strlen($decoded) > 1024 * 1024) {
            return false;
        }
        return true;
    }

    private function validateSignatureStatus(): bool {
        if ($this->signatureStatus === null) return true;
        $valid = ['pending', 'pre-trip-complete', 'complete'];
        return in_array($this->signatureStatus, $valid, true);
    }

    private function validateTextarea(string $value): string {
        // 1. Remove HTML Tags
        $clean = strip_tags($value);

        // 2. Remove ASCII control characters (except newline & tab)
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $clean);

        // 3. Trim whitespace
        return trim($clean);
    }
}

?>