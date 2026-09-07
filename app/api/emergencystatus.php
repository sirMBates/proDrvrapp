<?php

declare(strict_types=1);

use Core\Database;
use App\Repositories\EmergencyRepository;
use App\Repositories\DriverStatusRepository;
use App\Services\EmergencyService;

requireLoginAjax();

header('Content-Type: application/json');

$driverId = (int) ($_SESSION['user_id'] ?? 0);

if ($driverId <= 0) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required.'
    ]);
    exit();
}

try {
    $pdo = (new Database())->connect();

    $emergencyRepository = new EmergencyRepository($pdo);
    $driverStatusRepository = new DriverStatusRepository($pdo);
    $emergencyService = new EmergencyService($pdo, $emergencyRepository, $driverStatusRepository);

    $isActive = $emergencyService->hasActiveEmergency($driverId);

    echo json_encode([
        'status' => 'success',
        'emergency_active' => $isActive
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to retrieve Emergency state.'
    ]);
}

?>