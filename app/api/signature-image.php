<?php

declare(strict_types=1);

use Core\Database;
use App\Repositories\AssignmentRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit();
}

$driverId = (int) ($_SESSION['user_id'] ?? 0);

if ($driverId <= 0) {
    http_response_code(401);
    exit();
}

$orderId = filter_input(INPUT_GET, 'orderId', FILTER_VALIDATE_INT);
$assignmentControl = trim((string) ($_GET['assignmentControl'] ?? ''));
$signatureType = trim((string) ($_GET['type'] ?? ''));

if (!$orderId || $assignmentControl === '' || !in_array($signatureType, ['pre', 'post'], true)) {
    http_response_code(400);
    exit();
}

try {
    $pdo = (new Database())->connect();

    $assignmentRepository = new AssignmentRepository($pdo);

    $assignment = $assignmentRepository->findByIdentity($orderId, $driverId, $assignmentControl);

    if ($assignment === null) {
        http_response_code(404);
        exit();
    }

    $signaturePath = match ($signatureType) {
        'pre' => $assignment['pre_signature_path'] ?? null,
        'post' => $assignment['post_signature_path'] ?? null
    };

    if (!is_string($signaturePath) || $signaturePath === '') {
        http_response_code(404);
        exit();
    }

    $signatureRoot = base_path('storage/uploads/signatures');
    $filePath = $signatureRoot . DIRECTORY_SEPARATOR . $signaturePath;

    if (!is_file($filePath) || !is_readable($filePath)) {
        http_response_code(404);
        exit();
    }

    header('Content-Type: image/png');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: private, no-store');

    readfile($filePath);
    exit();
} catch (Throwable $e) {
    error_log('[SIGNATURE IMAGE ERROR] ' . $e::class . ': ' . $e->getMessage());
    http_response_code(500);
    exit();
}

?>