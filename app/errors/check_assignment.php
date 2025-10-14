<?php

header("Content-Type: application/json");

class UpdateAssignment {
    private $driverId;
    private $operatorId;
    private $orderId;
    private $vehicleId;
    private $assignmentStatus;

    public function __construct($driverId, $operatorId, $orderId, $vehicleId, $assignmentStatus) {
        $this->driverId = $driverId;
        $this->operatorId = $operatorId;
        $this->orderId = $orderId;
        $this->vehicleId = $vehicleId;
        $this->assignmentStatus = $assignmentStatus;
    }

    public function confirm() {
        if ($this->missingInfo()) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Please check all data before confirming.'
            ]);
            exit();
        }

        if (!$this->validateAssignment()) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Please check your assignment order id.'
            ]);
            exit();
        }
    }

    //public function cancel() {}

    //public function completeAssignment() {}

    private function missingInfo() {
        $result;
        if (empty($this->driverId) || empty($this->operatorId) || empty($this->orderId) || empty($this->vehicleId) || empty($this->assignmentStatus)) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }

    private function validateAssignment() {
        $result;
        $assignmentNumber = $this->orderId;
        function sanitizeAssignmentOrder($assignmentNumber) {
            $sanitize_orderNumber = filter_var($assignmentNumber, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);            
            return $sanitize_orderNumber;
        }
        $sanitizedAssignmentNumber = cleanStatus($currentStatus);
        if (!preg_match("/^[0-9]{1,}$/", $sanitizedAssignmentNumber)) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }
}

?>