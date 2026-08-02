<?php

declare(strict_types=1);

class GetWorkContr extends WorkAssignments {
    public function workInformation(): void {
        try {
            $driverId = (int) ($_SESSION['driver_id'] ?? 0);
            if ($driverId < 1) {
                http_response_code(401);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Driver session is unavailable.'
                ]);
                exit();
            }
            $assignments = $this->driverWorkAssignments($driverId);
            //dd($assignments);
            echo json_encode([
                'status' => 'success',
                'data' => $assignments
            ]);
            exit();
        } catch (\Throwable $exception) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Assignments could not be retrieved.' 
            ]);
            exit();
        }
    }
}

?>