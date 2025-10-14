<?php

header("Content-Type: application/json");

class UpdateAssignmentContr extends UpdateAssignment {
    private $driverId;
    private $orderId;
    private $vehicleId;
    private $assignmentStatus;

    public function __construct($driverId, $orderId, $vehicleId, $assignmentStatus) {
        $this->driverId = $driverId;
        $this->orderId = $orderId;
        $this->vehicleId = $vehicleId;
        $this->assignmentStatus = $assignmentStatus;
    }

    public function confirm() {
        if ($this->isInfoMissing()) {
            http_response_code(400);
            return [
                'status' => 'error',
                'message' => 'Please check all data before confirming.'
            ];
        }

        if (!$this->validateAssignment()) {
            http_response_code(400);
            return [
                'status' => 'error',
                'message' => 'Please check your assignment order id.'
            ];
        }

        if (!$this->validateVehicleId()) {
            http_response_code(400);
            return [
                'status' => 'error',
                'message' => 'Please check your vehicle id.'
            ];
        }

        if (!$this->validateAssignmentStatus()) {
            http_response_code(400);
            return [
                'status' => 'error',
                'message' => 'There was a problem with updating assignment status. Try again!'
            ];
        }

        return $this->confirmAssignment($this->driverId, $this->orderId, $this->vehicleId, $this->assignmentStatus);
    }

    //public function cancel() {}

    //public function completeAssignment() {}

    private function isInfoMissing(): bool {
        $checkInfo = [
            $this->driverId, 
            $this->orderId, 
            $this->vehicleId,
            $this->assignmentStatus
        ];
        foreach($checkInfo as $value) {
            if (empty($value)) {
                return true;
            }
        }
        return false;
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

    private function validateVehicleId(): bool {
        $currentVehicle = $this->vehicleId;
        function sanitizeIdOfVehicle($id) {
            $sanitizeId = filter_var($id, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);
            return $sanitizeId;
        }
        $sanitizedVehicleId = sanitizeIdOfVehicle($currentVehicle);
        if (!preg_match("/^[0-9]{3,}$/", $sanitizedVehicleId)) {
            return false;
        }
        return true; 
    }

    private function validateAssignmentStatus(): bool {
        $orderStatus = $this->assignmentStatus;
        $shrinkStatus = strtolower($orderStatus);
        function sanitizeConfirmationStatus($status) {
            $sanitizeStatus = filter_var($status, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);
            return $sanitizeStatus;
        }
        $sanitizedStatus = sanitizeConfirmationStatus($shrinkStatus);
        if (!preg_match("/^[a-z]{2,}$/", $sanitizedStatus)) {
            return false;
        }
        return true;
    }
}

?>