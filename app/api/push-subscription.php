<?php

declare(strict_types=1);

use App\Repositories\UserRepository;
use App\Repositories\PushSubscriptionRepository;
use App\Services\PushSubscriptionService;
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

try {
    $pdo = (new Database())->connect();

    $userRepository = new UserRepository($pdo);
    $subscriptionRepository = new PushSubscriptionRepository($pdo);

    $service = new PushSubscriptionService($userRepository, $subscriptionRepository);

    $service->subscribeForDriver($driverId, $input);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Message notifications enabled for this browser.'
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
    error_log('[PUSH SUBSCRIPTION] Save failed: ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Message notifications could not be enabled.'
    ]);
    exit();
}

?>