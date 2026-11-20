<?php

declare(strict_types=1);

use App\Services\UserSessionService;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed.'
    ]);
    exit;
}

requireLoginAjax();

$sessionService = new UserSessionService();
$session = $sessionService->findCurrent();

if ($session === null || !$sessionService->isCurrentValid((int) $_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Session expired or unavailable.'
    ]);
    exit;
}

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'expiresAt' => $session['expires_at'],
    'absoluteExpiresAt' => $session['absolute_expires_at']
]);

?>