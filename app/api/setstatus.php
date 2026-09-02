<?php

declare(strict_types=1);

use App\Repositories\DriverStatusRepository;
use App\Repositories\AssignmentRepository;
use App\Services\DriverStatusService;

requireLoginAjax();
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Method not allowed.'
        ]);
        exit();
    }

    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody, true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request data.'
        ]);
        exit();
    }

    $csrfToken = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $data['csrf_token'] ?? '');
    $sessionToken = (string) ($_SESSION['drvr_token'] ?? ''); 
    if ($csrfToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized request!'
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

    $driverStatus = trim((string) ($data['drvrStatus'] ?? ''));
    if ($driverStatus === '') {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Driver status is required.'
        ]);
        exit();
    }

    $repository = new DriverStatusRepository();
    $assignmentRepository = new AssignmentRepository();
    $service = new DriverStatusService($repository, $assignmentRepository);

    $statusRecord = $service->changeStatus($driverId, $driverStatus);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Your status has been updated!',
        'data' => $statusRecord
    ]);
    exit();
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit();
} catch (Throwable) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unexpected server error.'
    ]);
    exit();
}

?>