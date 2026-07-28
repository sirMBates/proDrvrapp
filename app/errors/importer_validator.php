<?php

declare(strict_types=1);

use core\Logger;

class ImporterAssignmentValidator {
    private array $errors = [];

    public function __construct(private readonly Logger $logger) {}
    /**
     * Validate one normalized Excel assignment row.
     */
    public function validate(array $data, ?int $excelRow = null): bool {
        $this->errors = [];

        $this->validateRequiredFields($data);
        $this->validateAssignmentControl($data);
        $this->validateOperatorId($data);
        $this->validateVehicleId($data);
        $this->validateOrderReference($data);
        $this->validateImportDateTimes($data);
        $this->validateImportTimes($data);
        $this->validateNumericFields($data);
        $this->validateSignatureRequired($data);
        $this->validateCompletionFieldsAreEmpty($data);
        $this->validateTextFields($data);
        $this->validateFormulaFields($data);

        if ($this->errors !== []) {
            $this->logValidationFailure($data, $excelRow);
            return false;
        }

        return true;
    }

    /**
     * Return all validation errors for the current row.
     */
    public function getErrors(): array {
        return $this->errors;
    }

    private function validateRequiredFields(array $data): void {
        $requiredFields = [
            'assignment_control',
            'operator_id',
            'order_ref',
            'vehicle_id',
            'start_date_time',
            'origin',
            'destination',
        ];

        foreach ($requiredFields as $field) {
            if ($this->isEmpty($data[$field] ?? null)) {
                $this->addError($field, $this->formatFieldName($field) . ' is required.');
            }
        }
    }

    private function validateAssignmentControl(array $data): void {
        $value = trim((string) ($data['assignment_control'] ?? ''));

        if ($value === '') {
            return;
        }

        if (!preg_match('/^PD-\d{8}-\d{6}-[A-Z0-9]+-\d{4}$/', $value)) {
            $this->addError('assignment_control', 'Assignment control has an invalid format.');
        }

        if (mb_strlen($value) > 50) {
            $this->addError('assignment_control', 'Assignment control cannot exceed 50 characters.');
        }
    }

    private function validateOperatorId(array $data): void {
        $value = trim((string) ($data['operator_id'] ?? ''));

        if ($value === '') {
            return;
        }

        if (mb_strlen($value) > 50) {
            $this->addError('operator_id', 'Operator ID cannot exceed 50 characters.');
        }

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $value)) {
            $this->addError('operator_id', 'Operator ID contains unsupported characters.');
        }
    }

    private function validateVehicleId(array $data): void {
        $value = trim((string) ($data['vehicle_id'] ?? ''));

        if ($value === '') {
            return;
        }

        if (mb_strlen($value) > 50) {
            $this->addError('vehicle_id', 'Vehicle ID cannot exceed 50 characters.');
        }

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $value)) {
            $this->addError('vehicle_id', 'Vehicle ID contains unsupported characters.');
        }
    }

    private function validateOrderReference(array $data): void {
        $value = trim((string) ($data['order_ref'] ?? ''));

        if ($value === '') {
            return;
        }

        if (mb_strlen($value) > 100) {
            $this->addError('order_ref', 'Order reference cannot exceed 100 characters.');
        }
    }

    private function validateImportDateTimes(array $data): void {
        $dateTimeFields = [
            'start_date_time',
            'leave_date_time',
            'return_date_drop_time',
            'end_date_time'
        ];

        foreach ($dateTimeFields as $field) {
            $value = $data[$field] ?? null;

            if ($this->isEmpty($value)) {
                continue;
            }

            if ($this->toDateTime($value) === null) {
                $this->addError($field, $this->formatFieldName($field) . ' contains an invalid date or time.');
            }
        }

        $this->validateDateTimeOrder($data);
    }

    private function validateDateTimeOrder(array $data): void {
        $start = $this->toDateTime($data['start_date_time'] ?? null);
        $leave = $this->toDateTime($data['leave_date_time'] ?? null);
        $return = $this->toDateTime($data['return_date_drop_time'] ?? null);
        $end = $this->toDateTime($data['end_date_time'] ?? null);

        if ($start !== null && $leave !== null && $leave < $start) {
            $this->addError('leave_date_time', 'Leave date and time cannot be before the start date and time.');
        }

        if ($leave !== null && $return !== null && $return < $leave) {
            $this->addError('return_date_drop_time', 'Return date and time cannot be before the leave date and time.');
        }

        if ($start !== null && $end !== null && $end < $start) {
            $this->addError('end_date_time', 'End date and time cannot be before the start date and time.');
        }
    }

    private function validateImportTimes(array $data): void {
        $timeFields = ['spot_time', ];

        foreach ($timeFields as $field) {
            $value = $data[$field] ?? null;

            if ($this->isEmpty($value)) {
                continue;
            }

            if (!$this->isValidTime($value)) {
                $this->addError($field, $this->formatFieldName($field) . ' contains an invalid time.');
            }
        }
    }

    private function validateNumericFields(array $data): void {
        $value = $data['num_of_coaches'] ?? null;

        if ($this->isEmpty($value)) {
            return;
        }

        $validatedValue = filter_var($value, FILTER_VALIDATE_INT);

        if ($validatedValue === false || $validatedValue < 1) {
            $this->addError('num_of_coaches', 'Number of coaches must be a non-negative whole number.');
        }
    }

    private function validateSignatureRequired(array $data): void {
        $value = $data['signature_required'] ?? null;

        if ($this->isEmpty($value)) {
            return;
        }

        $validatedValue = filter_var($value, FILTER_VALIDATE_INT);

        if ($validatedValue === false || !in_array($validatedValue, [0, 1], true)) {
            $this->addError('signature_required', 'Signature required must be either 0 or 1.');
        }
    }

    private function validateCompletionFieldsAreEmpty(array $data): void {
        $completionFields = [
            'actual_drop_time',
            'actual_end_time',
            'total_job_time',
            'driving_time',
            'pre_signature_path',
            'post_signature_path',
            'completed_at',
            'confirmed_at',
        ];

        foreach ($completionFields as $field) {
            if (!$this->isEmpty($data[$field] ?? null)) {
                $this->addError($field, $this->formatFieldName($field) . ' must be empty when the assignment is imported.');
            }
        }
    }

    private function validateTextFields(array $data): void {
        $textFields = [
            'origin' => 255,
            'destination' => 255,
            'group_name' => 255,
            'group_leader' => 150,
            'group_leader_mobile' => 30,
            'customer_name' => 255,
            'customer_phone' => 30,
            'contact_name' => 150,
            'contact_mobile' => 30,
            'pickup_details' => 2000,
            'destination_details' => 2000,
        ];

        foreach ($textFields as $field => $maxLength) {
            $value = trim((string) ($data[$field] ?? ''));

            if ($value === '') {
                continue;
            }

            if (mb_strlen($value) > $maxLength) {
                $this->addError($field, $this->formatFieldName($field) . " cannot exceed {$maxLength} characters.");
            }

            if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value)) {
                $this->addError($field, $this->formatFieldName($field) . ' contains unsupported control characters.');
            }
        }
    }

    /**
     * Reject formulas in fields that must contain plain text.
     */
    private function validateFormulaFields(array $data): void {
        $plainTextFields = [
            'operator_id',
            'order_ref',
            'vehicle_id',
            'origin',
            'destination',
            'group_name',
            'group_leader',
            'customer_name',
            'contact_name',
            'pickup_details',
            'destination_details',
        ];

        foreach ($plainTextFields as $field) {
            $value = ltrim((string) ($data[$field] ?? ''));

            if ($value === '') {
                continue;
            }

            $firstCharacter = mb_substr($value, 0, 1);

            if (in_array($firstCharacter, ['=', '+', '@'], true)) {
                $this->addError($field, $this->formatFieldName($field) . ' cannot contain an Excel formula.');
            }
        }
    }

    private function isValidTime(mixed $value): bool {
        $value = trim((string) $value);
        $formats = [
            'H:i:s',
            'H:i',
            'g:i A',
            'g:i a',
        ];

        foreach ($formats as $format) {
            $date = \DateTimeImmutable::createFromFormat('!' . $format, $value);
            $errors = \DateTimeImmutable::getLastErrors();

            if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return true;
            }
        }

        return false;
    }

    private function toDateTime(mixed $value): ?\DateTimeImmutable {
        if ($this->isEmpty($value)) {
            return null;
        }

        try {
            return new \DateTimeImmutable(trim((string) $value));
        } catch (\Throwable) {
            return null;
        }
    }

    private function isEmpty(mixed $value): bool {
        return $value === null || trim((string) $value) === '';
    }

    private function addError(string $field, string $message): void {
        $this->errors[$field][] = $message;
    }

    private function formatFieldName(string $field): string {
        return ucwords(str_replace('_', ' ', $field));
    }

    private function logValidationFailure(array $data, ?int $excelRow): void {
        $rowLabel = $excelRow !== null ? (string) $excelRow : 'unknown';

        $this->logger->error('[ASSIGNMENT IMPORT VALIDATION] ' . "Excel row {$rowLabel} failed validation. " . 'Assignment control: ' . ($data['assignment_control'] ?? 'not generated') . '. Operator ID: ' . ($data['operator_id'] ?? 'missing') . '. Order reference: ' . ($data['order_ref'] ?? 'missing') . '. Errors: ' . json_encode($this->errors, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}

?>