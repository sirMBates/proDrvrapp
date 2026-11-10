<?php

declare(strict_types=1);

use App\Services\DriverProfileService;

requireLoginAjax('driver');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://prodriver.local');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: X-CSRF-Token, Content-Type, X-Requested-With');

// Validate CSRF token.
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

// Retrieve the authenticated driver's ID.
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
    $driverProfileService = new DriverProfileService();

    $profile = $driverProfileService->driverProfile($driverId);

    http_response_code(200);
    echo json_encode($profile);
} catch (\RuntimeException $e) {
    error_log('Driver profile retrieval failed: ' . $e->getMessage());
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => 'Driver profile could not be retrieved.'
    ]);
} catch (\Throwable $e) {
    error_log('Driver profile error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to retrieve driver profile.'
    ]);
}

exit();

?>