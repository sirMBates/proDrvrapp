<?php

declare(strict_types=1);

use App\Repositories\TimesheetSubmissionRepository;
use App\Services\TimesheetHistoryService;
use Core\Database;

requireLoginAjax();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed.',
    ]);
    exit();
}

$headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
$sessionToken = $_SESSION['drvr_token'] ?? null;

if (!$headerToken || !$sessionToken || !hash_equals($sessionToken, $headerToken)) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied' // Invalid CSRF Token
    ]);
    exit();
}

$driverId = (int) ($_SESSION['user_id'] ?? 0);
if ($driverId < 1) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication is required.',
    ]);
    exit();
}

try {
    $pdo = (new Database())->connect();

    $submissionRepository = new TimesheetSubmissionRepository($pdo);

    $historyService = new TimesheetHistoryService($submissionRepository);

    $submissions = $historyService->getDriverHistory($driverId, 25);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Timesheet history loaded successfully.',
        'historyData' => [
            'submission_count' => count($submissions),
            'submissions' => $submissions,
        ],
    ]);
    exit();
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ]);
    exit();
} catch (Throwable $e) {
    error_log('[TIMESHEET HISTORY] Failed loading history: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Timesheet history could not be loaded.',
    ]);
    exit();
}

?>