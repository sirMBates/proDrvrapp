<?php

declare(strict_types=1);

use Core\Database;
use Core\Storage;
use App\Repositories\AssignmentRepository;
use App\Repositories\SignatureRequestRepository;
use App\Services\SignatureRequestService;
use App\Services\SignatureService;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed.'
    ]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'] ?? '';
$signature = $input['signature'] ?? '';

if ($token === '' || $signature === '') {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Signature token and signature are required.'
    ]);
    exit();
}

try {
    $pdo = (new Database())->connect();
    $storage = new Storage();

    $assignmentRepository = new AssignmentRepository($pdo);
    $signatureRequestRepository = new SignatureRequestRepository($pdo);

    $signatureRequestService = new SignatureRequestService($pdo, $signatureRequestRepository, $assignmentRepository);
    $signatureService = new SignatureService($assignmentRepository, $storage);

    $request = $signatureRequestService->getActiveRequestByToken($token);
    if ($request === null) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'This signature request is invalid or no longer active.'
        ]);
        exit();
    }

    $signatureData = $signatureService->saveSignature($request, $signature);

    $signatureRequestService->consumeRequest((int) $request['signature_request_id']);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Signature saved successfully.',
        'signatureData' => $signatureData
    ]);
    exit();
} catch (Throwable $e) {
    error_log('[SIGNATURE SUBMIT ERROR] ' . $e::class . ': ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'The signature submission could not be verified.'
    ]);
    exit();
}

?>