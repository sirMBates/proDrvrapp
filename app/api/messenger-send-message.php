<?php

declare(strict_types=1);

use App\Repositories\MessageRepository;
use App\Repositories\UserRepository;
use App\Services\MessengerMessageService;
use Core\Database;

requireLoginAjax();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
/*
 * Release the session lock before database work so Messenger
 * polling requests are not blocked by message submission.
 */
session_write_close();

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request.'
    ]);
    exit();
}

try {
    $pdo = (new Database())->connect();

    $userRepository = new UserRepository($pdo);
    $messageRepository = new MessageRepository($pdo);

    $service = new MessengerMessageService($userRepository, $messageRepository);

    $message = $service->sendForDriver($driverId, $input);
    /*
     * Use 200 for both a new message and an idempotent retry.
     * The same client UUID always resolves to the same message.
     */
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Message sent successfully.',
        'messageData' => $message
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
    error_log('[MESSENGER MESSAGE] Send failed: ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'The message could not be sent.'
    ]);
    exit();
}

?>