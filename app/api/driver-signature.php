<?php

declare(strict_types=1);

use App\Repositories\AssignmentRepository;
use App\Repositories\DriverStatusRepository;
use App\Services\AssignmentStatusVerificationService;
use App\Services\SignatureService;
use Core\Database;
use Core\Storage;

requireLoginAjax();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed.'
    ]);
    exit();
}

$driverId = (int) ($_SESSION['user_id'] ?? 0);
$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$sessionToken = $_SESSION['drvr_token'] ?? '';

if ($driverId <= 0 || $csrfToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied.'
    ]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

$orderId = filter_var($input['order_id'] ?? null, FILTER_VALIDATE_INT);
$assignmentControl = trim((string) ($input['assignment_control'] ?? ''));
$signatureType = trim((string) ($input['signature_type'] ?? ''));
$signature = trim((string) ($input['signature'] ?? ''));

if ($orderId === false || $orderId <= 0 || $assignmentControl === '' || !in_array($signatureType, ['pre', 'post'], true) || $signature === '') {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid signature submission.'
    ]);
    exit();
}

try {
    $pdo = (new Database())->connect();

    $assignmentRepository = new AssignmentRepository($pdo);
    $driverStatusRepository = new DriverStatusRepository($pdo);

    $assignmentStatusVerificationService = new AssignmentStatusVerificationService($assignmentRepository, $driverStatusRepository);

    $storage = new Storage();

    $signatureService = new SignatureService($pdo, $assignmentRepository, $assignmentStatusVerificationService, $storage);

    $request = [
        'order_id' => (int) $orderId,
        'driver_id' => $driverId,
        'assignment_control' => $assignmentControl,
        'signature_type' => $signatureType
    ];

    $signatureData = $signatureService->saveSignature($request, $signature);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => ucfirst($signatureType) . '-inspection signature saved successfully.',
        'signatureData' => $signatureData
    ]);
    exit();
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit();
} catch (\Throwable $e) {
    error_log('[DRIVER SIGNATURE ERROR] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'The signature could not be saved.'
    ]);
    exit();
}

?>