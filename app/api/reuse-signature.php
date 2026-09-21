<?php

declare(strict_types=1);

use Core\Database;
use Core\Storage;
use App\Repositories\AssignmentRepository;
use App\Services\SignatureService;

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
if ($driverId <= 0) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required.'
    ]);
    exit();
}

$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$sessionToken = $_SESSION['drvr_token'] ?? '';

if ($csrfToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid security token.'
    ]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request data.'
    ]);
    exit();
}

$orderId = filter_var($input['order_id'] ?? null, FILTER_VALIDATE_INT);
$assignmentControl = trim((string) ($input['assignment_control'] ?? ''));

if (!$orderId || $assignmentControl === '') {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Assignment information is required.'
    ]);
    exit();
}

try {
    $pdo = (new Database())->connect();

    $assignmentRepository = new AssignmentRepository($pdo);
    $storage = new Storage();

    $signatureService = new SignatureService( $assignmentRepository, $storage);

    $signatureData = $signatureService->reusePreSignatureAsPost($orderId, $driverId, $assignmentControl);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Pre-inspection signature reused successfully.',
        'signatureData' => $signatureData
    ]);
    exit();
} catch (\Throwable $e) {
    error_log('[SIGNATURE REUSE ERROR] ' . $e::class . ': ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'The signature could not be reused.'
    ]);
    exit();
}

?>