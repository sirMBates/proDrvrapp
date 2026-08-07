<?php

use App\Validation\Validator;
use App\Validation\AssignmentValidator;
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

    //

    public function confirm(): array {
        if ($this->isInfoMissing()) {
            http_response_code(400);
            return [
                'status' => 'error',
                'message' => 'Please check all data before confirming.'
            ];
        }

        if (!$this->validateAssignmentControl()) {
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
                'message' => 'Please check your assignment id.'
            ];
        }

        if (!$this->validateDriverId()) {
            http_response_code(400);
            return [
                'status' => 'error',
                'message' => 'The driver information is invalid.'
            ];
        }

        $assignment = $this->getAssignmentByIdentity($this->assignmentControl, $this->orderId, $this->driverId);
        if (!$assignment) {
            http_response_code(404);
            return [
                'status' => 'error',
                'message' => 'The assignment could not be found.'
            ];
        }

        if (!AssignmentValidator::canConfirm($assignment)) {
            http_response_code(409);
            return [
                'status' => 'error',
                'message' => 'This assignment cannot be confirmed in it\'s current state.'
            ]
        }

        return $this->confirmAssignment($this->assignmentControl, $this->orderId, $this->driverId);
    }

    public function cancel(): array {
        if ($this->isInfoMissing()) {
            http_response_code(400);
            return [
                'status' => 'error',
                'message' => 'Please check all data before cancelling.'
            ];
        }

        if (!$this->validateAssignmentControl()) {
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
                'message' => 'Please check your assignment id.'
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
            $this->orderId, 
            $this->driverId 
        ];

        foreach($checkInfo as $value) {
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
}

?>