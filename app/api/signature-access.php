<?php

declare(strict_types=1);

use Core\Database;
use App\Repositories\AssignmentRepository;
use App\Repositories\SignatureRequestRepository;
use App\Services\SignatureRequestService;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed.'
    ]);
    exit();
}

$rawToken = trim((string) ($_GET['token'] ?? ''));
if ($rawToken === '') {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Signature token is required.'
    ]);
    exit();
}

try {
    $pdo = (new Database())->connect();

    $assignmentRepository = new AssignmentRepository($pdo);
    $signatureRequestRepository = new SignatureRequestRepository($pdo);
    $signatureRequestService = new SignatureRequestService($pdo, $signatureRequestRepository, $assignmentRepository);

    $request = $signatureRequestService->getActiveRequestByToken($rawToken);

    if ($request === null) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'This signature request is invalid or no longer active.'
        ]);
        exit();
    }

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Signature request is active.',
        'signatureData' => [
            'signature_request_id' => $request['signature_request_id'],
            'signature_type' => $request['signature_type'],
            'assignment_control' => $request['assignment_control'],
            'expires_at' => $request['expires_at']
        ]
    ]);
    exit();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'The signature request could not be verified.'
    ]);
    exit();
}

?>