<?php

declare(strict_types=1);

use App\Services\WorkAssignmentService;

class GetOperationalAssignmentContr {
    private WorkAssignmentService $assignmentService;

    public function __construct() {
        $this->assignmentService = new WorkAssignmentService();
    }

    public function assignmentInformation(): void {
        try {
            $driverId = (int) ($_SESSION['user_id'] ?? 0);

            if ($driverId < 1) {
                http_response_code(401);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Driver session is unavailable.'
                ]);
                exit();
            }

            $assignments = $this->assignmentService->operationalDriverAssignments($driverId);
            echo json_encode([
                'status' => 'success',
                'data' => $assignments
            ]);
            exit();
        } catch (\Throwable $exception) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Operational assignments could not be retrieved.'
            ]);
            exit();
        }
    }
}

?>