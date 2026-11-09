<?php

declare(strict_types=1);

use App\Services\WorkAssignmentService;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://prodriver.local');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: X-CSRF-Token, Content-Type, X-Requested-With');

requireLoginAjax();

$headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
$sessionToken = $_SESSION['drvr_token'] ?? null;

if (!is_string($headerToken) || !is_string($sessionToken) || $headerToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $headerToken)) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied'
    ]);
    exit();
}

$driverId = (int) ($_SESSION['user_id'] ?? 0);
if ($driverId < 1) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Driver session is unavailable.'
    ]);
    exit();
}

try {
    $assignmentService = new WorkAssignmentService();

    $assignments = $assignmentService->operationalDriverAssignments($driverId);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => $assignments
    ]);
} catch (\Throwable $exception) {
    error_log('Operational assignment retrieval failed: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Operational assignments could not be retrieved.'
    ]);
}

exit();

?>