<?php

declare(strict_types=1);

use App\Repositories\ConversationRepository;
use App\Repositories\UserRepository;
use App\Services\MessengerConversationService;
use Core\Database;

requireLoginAjax();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed.'
    ]);
    exit();
}

$driverId = (int) ($_SESSION['user_id'] ?? 0);
if ($driverId < 1 || ($_SESSION['logged_in'] ?? false) !== true) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required.'
    ]);
    exit();
}

$headerToken = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
$sessionToken = (string) ($_SESSION['drvr_token'] ?? '');

if ($headerToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $headerToken)) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied!'
    ]);
    exit();
}

session_write_close();

try {
    $input = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request data.'
    ]);
    exit();
}

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request data.'
    ]);
    exit();
}

try {
    $pdo = (new Database())->connect();

    $userRepository = new UserRepository($pdo);
    $conversationRepository = new ConversationRepository($pdo);

    $service = new MessengerConversationService($userRepository, $conversationRepository);

    $conversation = $service->createGroupForDriver($driverId, $input);

    http_response_code(201);
    echo json_encode([
        'status' => 'success',
        'conversation' => $conversation
    ]);
    exit();
} catch (DomainException $e) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
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
    error_log('[MESSENGER GROUP] Creation failed: ' . $e::class . ': ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'The group could not be created.'
    ]);
    exit();
}

?>