<?php

declare(strict_types=1);

use App\Services\DriverProfileService;

header("Content-Type: application/json; charset=utf-8");
//header("Access-Control-Allow-Origin: *");

class GetDrvrContr {
        private DriverProfileService $driverProfileService;

        public function __construct() {
                $this->driverProfileService = new DriverProfileService();
        }

        public function driverInfo(): void {
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

                        $operator = $this->driverProfileService->driverProfile($driverId);
                        echo json_encode($operator);
                        exit();
                } catch (Exception $e) {
                        http_response_code(404);
                        echo json_encode([
                                'status' => 'error',
                                'message' => 'Driver profile could not be retrieved.'
                        ]);
                        exit();
                }
        }
}

?>