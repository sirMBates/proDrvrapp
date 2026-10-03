<?php

declare(strict_types=1);

use App\Repositories\AssignmentRepository;
use App\Repositories\DriverStatusRepository;
use App\Repositories\TimesheetRepository;
use App\Repositories\TimesheetSubmissionRepository;
use App\Repositories\UserRepository;
use App\Services\PayPeriodService;
use App\Services\TimesheetService;
use App\Services\TimesheetSubmissionService;
use Core\Database;

requireLoginAjax();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed.'
    ]);
    exit();
}

$driverId = (int) ($_SESSION['user_id'] ?? 0);
if ($driverId < 1) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication is required.'
    ]);
    exit();
}

$reviewSession = $_SESSION['timesheet_review'] ?? null;

if (!is_array($reviewSession)) {
    http_response_code(409);
    echo json_encode([
        'status' => 'error',
        'message' => 'Begin the review from your Timesheet page.'
    ]);
    exit();
}

$reviewDriverId = (int) ($reviewSession['driver_id'] ?? 0);
$periodStart = trim((string) ($reviewSession['period_start'] ?? ''));
$periodEnd = trim((string) ($reviewSession['period_end'] ?? ''));
$reviewCreatedAt = (int) ($reviewSession['created_at'] ?? 0);
$reviewLifetimeSeconds = 15 * 60;

if ($reviewDriverId !== $driverId || $reviewCreatedAt < 1 || (time() - $reviewCreatedAt) > $reviewLifetimeSeconds) {
    unset($_SESSION['timesheet_review']);

    http_response_code(409);
    echo json_encode([
        'status' => 'error',
        'message' => 'Your Timesheet review has expired. Please begin again.'
    ]);
    exit();
}

try {
    $pdo = (new Database())->connect();

    $timesheetRepository = new TimesheetRepository($pdo);
    $driverStatusRepository = new DriverStatusRepository($pdo);
    $submissionRepository = new TimesheetSubmissionRepository($pdo);
    $assignmentRepository = new AssignmentRepository($pdo);
    $userRepository = new UserRepository($pdo);

    $payPeriodService = new PayPeriodService();
    $timesheetService = new TimesheetService($timesheetRepository, $driverStatusRepository, $payPeriodService);
    $submissionService = new TimesheetSubmissionService($submissionRepository, $assignmentRepository, $timesheetService, $payPeriodService, $userRepository);

    $reviewData = $submissionService->prepareCurrentReview($driverId, $periodStart, $periodEnd, 6, 'America/New_York');

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Timesheet review loaded successfully.',
        'reviewData' => $reviewData
    ]);
    exit();
} catch (InvalidArgumentException $e) {
    unset($_SESSION['timesheet_review']);

    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit();
} catch (PDOException $e) {
    error_log('[TIMESHEET REVIEW] Database failure: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'The Timesheet review could not be loaded.'
    ]);
    exit();
} catch (RuntimeException $e) {
    unset($_SESSION['timesheet_review']);

    http_response_code(409);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit();
} catch (Throwable $e) {
    error_log('[TIMESHEET REVIEW] Failed loading review: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'The Timesheet review could not be loaded.'
    ]);
    exit();
}