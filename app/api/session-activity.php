<?php

declare(strict_types=1);

use App\Services\UserSessionService;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed.'
    ]);
    exit();
}

requireLoginAjax();

$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

if (!is_string($csrfToken) || !hash_equals((string) ($_SESSION['user_token'] ?? ''), $csrfToken) || $csrfToken === '') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid security token.'
    ]);
    exit();
}

try {
    $sessionService = new UserSessionService();

    if (!$sessionService->refresh()) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Your session has expired. Please sign in again.'
        ]);
        exit();
    }

    $session = $sessionService->findCurrent();

    echo json_encode([
        'status' => 'success',
        'expiresAt' => $session['expires_at'],
        'absoluteExpiresAt' => $session['absolute_expires_at']
    ]);
} catch (\Throwable $exception) {
    error_log('[SESSION ACTIVITY ERROR] ' . $exception::class . ': ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to renew your session.'
    ]);
}

?>