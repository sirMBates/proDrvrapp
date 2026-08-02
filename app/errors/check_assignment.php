<?php

header("Content-Type: application/json");

class UpdateAssignmentContr extends UpdateAssignment {
    private string $assignmentControl;
    private int $orderId;
    private int $driverId;

    public function __construct(string $assignmentControl, int $orderId, int $driverId) {
        $this->assignmentControl = trim($assignmentControl);
        $this->orderId = $orderId;
        $this->driverId = $driverId;
    }

    public function confirm(): array {
        if ($this->isInfoMissing()) {
            http_response_code(400);
            return [
                'status' => 'error',
                'message' => 'Please check all data before confirming.'
            ];
        }

        if (!$this->validateAssignmentContl()) {
            http_response_code(400);
            return [
                'status' => 'error',
                'message' => 'System error. Please contact dispatch.'
            ];
        }

        if (!$this->validateAssignment()) {
            http_response_code(400);
            return [
                'status' => 'error',
                'message' => 'Please check your assignment order id.'
            ];
        }

        if (!$this->validateDriverId()) {
            http_response_code(400);
            return [
                'status' => 'error',
                'message' => 'The driver information is invalid.'
            ];
        }

        return $this->confirmAssignment($this->assignmentControl, $this->orderId, $this->driverId);
    }

    public function cancel() {
        if ($this->isInfoMissing(true)) {
            http_response_code(400);
            return [
                'status' => 'error',
                'message' => 'Please check all data before cancelling.'
            ];
        }

        if (!$this->validateAssignmentContl()) {
            http_response_code(400);
            return [
                'status' => 'error',
                'message' => 'System error. Please contact dispatch.'
            ];
        }

        if (!$this->validateAssignment()) {
            http_response_code(400);
            return [
                'status' => 'error',
                'message' => 'Please check your assignment order id.'
            ];
        }

        if (!$this->validateDriverId()) {
            http_response_code(400);
            return [
                'status' => 'error',
                'message' => 'The driver information is invalid.'
            ];
        }

        return $this->cancelAssignment($this->assignmentControl, $this->orderId, $this->driverId);
    }

    private function isInfoMissing(): bool {
        $checkInfo = [
            $this->assignmentControl,
            $this->driverId, 
            $this->orderId, 
        ];

        foreach($checkInfo as $value) {
            if (empty($value)) {
                return true;
            }
        }
        return false;
    }

    private function validateAssignmentContl(): bool {
        $control = filter_var($this->assignmentControl, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);

        if (!preg_match('/^PD-\d{8}-\d{6}-[A-F0-9]{4}-\d{4}$/', $control)) {
            return false;
        }
        return true;
    }

    private function validateAssignment(): bool {
        $assignmentNumber = $this->orderId;
        function sanitizeAssignmentOrder($assignedNumber) {
            $sanitize_orderNumber = filter_var($assignedNumber, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);                        
            return $sanitize_orderNumber;
        }
        $sanitizedAssignmentNumber = sanitizeAssignmentOrder($assignmentNumber);
        if (!preg_match("/^[0-9]{1,}$/", $sanitizedAssignmentNumber)) {
            return false;
        }
        return true;
    }

    private function validateDriverId(): bool {
        return filter_var($this->driverId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
    }
}

?>