<?php

declare(strict_types=1);

use App\Services\PreferenceService;

//requireLoginAjax();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

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

try {

    $service = new PreferenceService();
    $preferences = $service->getPreferences($driverId);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => [
            'preferences' => $preferences
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