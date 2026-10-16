<?php

declare(strict_types=1);

use App\Repositories\ConversationRepository;
use App\Repositories\UserRepository;
use App\Services\MessengerConversationService;
use Core\Database;

requireLoginAjax();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
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

if ($conversationId === false || $conversationId === null) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => 'A valid group conversation is required.'
    ]);
    exit();
}

try {
    $pdo = (new Database())->connect();

    $userRepository = new UserRepository($pdo);
    $conversationRepository = new ConversationRepository($pdo);

    $service = new MessengerConversationService($userRepository, $conversationRepository);

    $memberData = $service->groupMembersForDriver($driverId, (int) $conversationId);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'memberData' => $memberData
    ], JSON_THROW_ON_ERROR);
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
    error_log('[MESSENGER GROUP MEMBERS] Load failed: ' . $e::class);
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Group members could not be loaded.'
    ]);
    exit();
}

?>