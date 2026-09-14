<?php

declare(strict_types=1);

use Core\Database;
use App\Repositories\AssignmentRepository;
use App\Repositories\SignatureRequestRepository;
use App\Services\SignatureRequestService;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
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

$headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$sessionToken = $_SESSION['drvr_token'] ?? '';

if ($headerToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $headerToken)) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied!'
    ]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request.'
    ]);
    exit();
}

$orderId = filter_var($input['order_id'] ?? null, FILTER_VALIDATE_INT);
$assignmentControl = trim((string) ($input['assignment_control'] ?? ''));
$signatureType = trim((string) ($input['signature_type'] ?? ''));

if ($orderId === false || $orderId <= 0 || $assignmentControl === '' || $signatureType === '') {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid signature request.'
    ]);
    exit();
}

try {
    $pdo = (new Database())->connect();

    $assignmentRepository = new AssignmentRepository($pdo);
    $signatureRequestRepository = new SignatureRequestRepository($pdo);

    $signatureRequestService = new SignatureRequestService($pdo, $signatureRequestRepository, $assignmentRepository);
    $request = $signatureRequestService->createRequest((int) $orderId, $driverId, $assignmentControl, $signatureType);

    $signingPath = '/signature?token=' . rawurlencode($request['token']);

    http_response_code(201);
    echo json_encode([
        'status' => 'success',
        'message' => ucfirst($signatureType) . '-trip signature request created.',
        'signatureData' => [
            'signature_request_id' => $request['signature_request_id'],
            'signature_type' => $request['signature_type'],
            'signing_path' => $signingPath,
            'expires_at' => $request['expires_at'],
            'expires_in_seconds' => $request['expires_in_seconds'],
        ]
    ]);
    exit();
} catch (RuntimeException $e) {
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
        'message' => 'The signature request could not be created.'
    ]);
    exit();
}

?>