<?php

declare(strict_types=1);

use App\Repositories\MessageRepository;
use App\Repositories\UserRepository;
use App\Services\MessengerMessageService;
use Core\Database;

requireLoginAjax();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
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

$conversationId = filter_var($_GET['conversation_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$afterMessageId = filter_var($_GET['after_message_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

if ($conversationId === false) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => 'A valid conversation is required.'
    ]);
    exit();
}

if ($afterMessageId === false) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => 'The message cursor is invalid.'
    ]);
    exit();
}

try {
    $pdo = (new Database())->connect();

    $userRepository = new UserRepository($pdo);
    $messageRepository = new MessageRepository($pdo);

    $service = new MessengerMessageService($userRepository, $messageRepository);

    $messageData = $service->getForDriver($driverId, (int) $conversationId, (int) $afterMessageId, 50);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'messageData' => $messageData
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
    error_log('[MESSENGER MESSAGES] Load failed: ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Messages could not be loaded.'
    ]);
    exit();
}

?>