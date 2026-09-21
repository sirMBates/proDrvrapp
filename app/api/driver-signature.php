<?php

declare(strict_types=1);

use App\Repositories\AssignmentRepository;
use App\Services\SignatureService;
use Core\Database;
use Core\Storage;

header('Content-Type: application/json');

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

    $storage = new Storage();

    $signatureService = new SignatureService($assignmentRepository, $storage);

    /*
     * Build the same trusted request structure expected by
     * SignatureService::saveSignature().
     *
     * driver_id comes from the authenticated session,
     * never from JavaScript.
     */
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