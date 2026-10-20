<?php

declare(strict_types=1);

use Core\Database;
use App\Repositories\AssignmentRepository;
use App\Services\AssignmentOperationalGate;

requireLoginAjax();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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

$orderId = filter_var($_GET['orderId'] ?? null, FILTER_VALIDATE_INT);
$assignmentControl = trim((string) ($_GET['assignmentControl'] ?? ''));

if ($orderId === false || $orderId <= 0 || $assignmentControl === '') {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid assignment information.'
    ]);
    exit();
}

try {
    $pdo = (new Database())->connect();

    $assignmentRepository = new AssignmentRepository($pdo);
    $assignmentOperationalGate = new AssignmentOperationalGate();

    $assignment = $assignmentRepository->findByIdentity($orderId, $driverId, $assignmentControl);

    if ($assignment === null) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Assignment not found.'
        ]);
        exit();
    }

    $assignmentOperationalGate->assertGarageReportReached($assignment);

    echo json_encode([
        'status' => 'success',
        'operational' => true
    ]);
    exit();
} catch (InvalidArgumentException $e) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'operational' => false,
        'message' => $e->getMessage()
    ]);
    exit();
} catch (Throwable $e) {
    error_log('[ASSIGNMENT OPERATIONAL ACCESS ERROR] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'operational' => false,
        'message' => 'Unable to verify assignment operational access.'
    ]);
    exit();
}

?>