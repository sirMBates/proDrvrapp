<?php

declare(strict_types=1);

use App\Repositories\DriverStatusRepository;
use App\Repositories\TimesheetRepository;
use App\Services\PayPeriodService;
use App\Services\TimesheetService;
use Core\Database;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// requireLoginAjax();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

$entries = $input['entries'] ?? null;
if (!is_array($entries)) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid Timesheet entries.'
    ]);
    exit();
}

try {
    $pdo = (new Database())->connect();

    $timesheetRepository = new TimesheetRepository($pdo);
    $driverStatusRepository = new DriverStatusRepository($pdo);

    $payPeriodService = new PayPeriodService();
    $timesheetService = new TimesheetService($timesheetRepository, $driverStatusRepository, $payPeriodService);

    $lockData = $timesheetService->saveAndLockDriverEntries($driverId, $entries);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Timesheet entries saved and locked.',
        'lockData' => $lockData
    ]);
    exit();
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit();
} catch (PDOException $e) {
    error_log('[TIMESHEET] Database failure while saving and locking: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'The Timesheet entries could not be saved.'
    ]);
    exit();
} catch (RuntimeException $e) {
    http_response_code(409);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit();
} catch (Throwable $e) {
    error_log('[TIMESHEET] Failed saving and locking Timesheet: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'The Timesheet entries could not be saved.'
    ]);
    exit();
}