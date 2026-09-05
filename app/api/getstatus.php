<?php

declare(strict_types=1);

use Core\Database;
use App\Repositories\DriverStatusRepository;
use App\Repositories\AssignmentRepository;
use App\Repositories\EmergencyRepository;
use App\Services\DriverStatusService;
use App\Services\EmergencyService;

requireLoginAjax();
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Method not allowed.'
        ]);
        exit();
    }

    $driverId = (int) ($_SESSION['user_id'] ?? 0);

    if ($driverId < 1) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid user session.'
        ]);
        exit();
    }

    $pdo = (new Database())->connect();

    $driverStatusRepository = new DriverStatusRepository($pdo);
    $assignmentRepository = new AssignmentRepository($pdo);
    $emergencyRepository = new EmergencyRepository($pdo);

    $emergencyService = new EmergencyService($pdo, $emergencyRepository, $driverStatusRepository);
    $service = new DriverStatusService($driverStatusRepository, $assignmentRepository, $emergencyService);

    $currentStatus = $service->getCurrentStatus($driverId);
    $recentHistory = $service->getRecentHistory($driverId, 20);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => [
            'currentStatus' => $currentStatus,
            'recentHistory' => $recentHistory
        ] 
    ]);
    exit();
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unexpected server error.'
    ]);
    exit();
}

?>