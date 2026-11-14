<?php

declare(strict_types=1);

use App\Services\DispatchService;

requireLoginAjax('dispatch');

// Validate CSRF token.
$headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
$sessionToken = $_SESSION['user_token'] ?? null;

if (!is_string($headerToken) || !is_string($sessionToken) || $headerToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $headerToken)) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied.'
    ]);
    exit();
}

// Retrieve the authenticated dispatcher's ID.
$dispatchId = (int) ($_SESSION['user_id'] ?? 0);
if ($dispatchId < 1) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Dispatch session is unavailable.'
    ]);
    exit();
}

try {
    $dispatchService = new DispatchService();

    $profile = $dispatchService->getProfile($dispatchId);

    http_response_code(200);
    echo json_encode($profile);
} catch (\RuntimeException $e) {
    error_log('Dispatch profile retrieval failed: ' . $e->getMessage());
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => 'Dispatch profile could not be retrieved.'
    ]);
} catch (\Throwable $e) {
    error_log('Dispatch profile error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to retrieve dispatch profile.'
    ]);
}

exit();

?>
