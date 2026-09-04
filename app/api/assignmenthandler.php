<?php

requireLoginAjax();

use Core\Database;
use App\Repositories\EmergencyRepository;
use App\Repositories\DriverStatusRepository;
use App\Services\EmergencyService;

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: https://prodriver.local");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: X-CSRF-Token, Content-Type, X-Requested-With");
header("Access-Control-Allow-Methods: POST, PATCH, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}


$headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
$sessionToken = $_SESSION['drvr_token'] ?? null;

if ($sessionToken === null) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'No session token found.'
    ]);
    exit();
}

if (!$headerToken || !hash_equals($sessionToken, $headerToken)) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied!' // Invalid CSRF Token
    ]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($_POST['__method'])) {
    $override = strtoupper(trim((string) $_POST['__method']));
    
    if ($override === 'PATCH') {
        $method = 'PATCH';
    }
}

if ($method !== 'PATCH') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed.'
    ]);
    exit();
}

include_once base_path("app/models/assignmenthandlermodel.php");
include_once base_path("app/SubmissionHandlers/check_assignment.php");

$assignmentControl = trim((string) ($_POST['assignment_control'] ?? ''));
$orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
$driverId = (int) ($_SESSION['user_id'] ?? 0);

if ($orderId === false || $orderId < 1 || $driverId < 1) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid assignment request.'
    ]);
    exit();
}

$pdo = (new Database())->connect();
$emergencyRepository = new EmergencyRepository($pdo);
$driverStatusRepository = new DriverStatusRepository($pdo);
$emergencyService = new EmergencyService($pdo, $emergencyRepository, $driverStatusRepository);

$assignmentController = new UpdateAssignmentContr($assignmentControl, $orderId, $driverId, $emergencyService);
$confirmRequested = isset($_POST['confirm']);
$cancelRequested = isset($_POST['cancel']);

if ($confirmRequested === $cancelRequested) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Exactly one assignment action must be provided.'
    ]);
    exit();
}

try {
    $result = $confirmRequested ? $assignmentController->confirm() : $assignmentController->cancel();
    echo json_encode($result);
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