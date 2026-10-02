<?php

declare(strict_types=1);

use App\Repositories\AssignmentRepository;
use App\Repositories\DriverStatusRepository;
use App\Repositories\TimesheetRepository;
use App\Repositories\TimesheetSubmissionRepository;
use App\Services\PayPeriodService;
use App\Services\TimesheetService;
use App\Services\TimesheetSubmissionService;
use Core\Database;
use Core\Flash;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit();
}

$driverId = (int) ($_SESSION['user_id'] ?? 0);
if ($driverId < 1) {
    Flash::setMsg('danger', 'You must be logged in to review a Timesheet.');
    header('Location: /signin');
    exit();
}

$formToken = trim((string) ($_POST['drvrtoken'] ?? ''));
$sessionToken = (string) ($_SESSION['drvr_token'] ?? '');

if ($formToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $formToken)) {
    Flash::setMsg('danger', 'Please retry your Timesheet request.');
    header('Location: /timesheet?danger=review+expired');
    exit();
}

$periodStart = trim((string) ($_POST['period_start'] ?? ''));
$periodEnd = trim((string) ($_POST['period_end'] ?? ''));

try {
    $pdo = (new Database())->connect();

    $timesheetRepository = new TimesheetRepository($pdo);
    $driverStatusRepository = new DriverStatusRepository($pdo);
    $submissionRepository = new TimesheetSubmissionRepository($pdo);
    $assignmentRepository = new AssignmentRepository($pdo);

    $payPeriodService = new PayPeriodService();
    $timesheetService = new TimesheetService($timesheetRepository, $driverStatusRepository, $payPeriodService);
    $submissionService = new TimesheetSubmissionService($submissionRepository, $assignmentRepository, $timesheetService, $payPeriodService);

    /*
     * Validate before creating the review session.
     * The GET review controller will validate everything again.
     */
    $review = $submissionService->prepareCurrentReview($driverId, $periodStart, $periodEnd, 6, 'America/New_York');

    $_SESSION['timesheet_review'] = [
        'driver_id' => $driverId,
        'period_start' => $review['period_start'],
        'period_end' => $review['period_end'],
        'created_at' => time(),
    ];

    Flash::setMsg('info', 'Review your Timesheet carefully before final submission.');
    header('Location: /timesheet-review');
    exit();
} catch (InvalidArgumentException $e) {
    unset($_SESSION['timesheet_review']);

    Flash::setMsg('warning', $e->getMessage());
    header('Location: /timesheet?warning=invalid+review');
    exit();
} catch (RuntimeException $e) {
    unset($_SESSION['timesheet_review']);

    Flash::setMsg('warning', $e->getMessage());
    header('Location: /timesheet?warning=review+unavailable');
    exit();
} catch (Throwable $e) {
    unset($_SESSION['timesheet_review']);

    error_log('[TIMESHEET REVIEW] Failed preparing review: ' . $e->getMessage());
    Flash::setMsg('danger', 'The Timesheet review could not be prepared.');
    header('Location: /timesheet?danger=review+failed');
    exit();
}

?>