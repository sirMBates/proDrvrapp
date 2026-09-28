<?php

declare(strict_types=1);

use App\Repositories\DriverStatusRepository;
use App\Repositories\TimesheetRepository;
use App\Services\PayPeriodService;
use App\Services\TimesheetService;
use Core\Database;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

//requireLoginAjax();

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

try {
    $pdo = (new Database())->connect();

    $timesheetRepository = new TimesheetRepository($pdo);
    $driverStatusRepository = new DriverStatusRepository($pdo);
    $payPeriodService = new PayPeriodService();

    $timesheetService = new TimesheetService($timesheetRepository, $driverStatusRepository, $payPeriodService);
    $timesheetData = $timesheetService->getCurrentDriverPeriod($driverId, 6, 'America/New_York');

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Timesheet loaded successfully.',
        'timesheetData' => $timesheetData
    ]);
    exit();
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit();
} catch (Throwable $e) {
    error_log('[TIMESHEET] Failed loading Timesheet: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'The Timesheet could not be loaded.'
    ]);
    exit();
}