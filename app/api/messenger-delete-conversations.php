<?php

declare(strict_types=1);

use App\Repositories\UserRepository;
use App\Repositories\ConversationRepository;
use App\Services\MessengerConversationService;
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

$selectedIds = $input['conversation_ids'] ?? null;
if (!is_array($selectedIds) || !array_is_list($selectedIds) || count($selectedIds) < 1 || count($selectedIds) > 100) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => 'Select between 1 and 100 conversations.'
    ]);
    exit();
}

$conversationIds = [];

foreach ($selectedIds as $selectedId) {
    if (!is_int($selectedId) || $selectedId < 1) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'A selected conversation ID is invalid.'
        ]);
        exit();
    }

    $conversationIds[] = $selectedId;
}

try {
    $pdo = (new Database())->connect();

    $userRepository = new UserRepository($pdo);
    $conversationRepository = new ConversationRepository($pdo);

    $service = new MessengerConversationService($userRepository, $conversationRepository);

    $deletionData = $service->deleteForDriver($driverId, $conversationIds);

    $json = json_encode([
        'status' => 'success',
        'message' => 'Selected conversations were cleared from your view.',
        'deletionData' => $deletionData
    ], JSON_THROW_ON_ERROR);

    http_response_code(200);
    echo $json;
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
    error_log('[MESSENGER DELETE] Failed: ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'The conversations could not be deleted.'
    ]);
    exit();
}

?>