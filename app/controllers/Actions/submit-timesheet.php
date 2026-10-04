<?php

declare(strict_types=1);

use App\Repositories\AssignmentRepository;
use App\Repositories\DriverStatusRepository;
use App\Repositories\TimesheetRepository;
use App\Repositories\TimesheetSubmissionRepository;
use App\Repositories\UserRepository;
use App\Services\PayPeriodService;
use App\Services\TimesheetFinalizationService;
use App\Services\TimesheetPdfRenderer;
use App\Services\TimesheetService;
use App\Services\TimesheetSubmissionService;
use Core\Database;
use Core\Flash;
use Core\Storage;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit();
}

$driverId = (int) ($_SESSION['user_id'] ?? 0);
if ($driverId < 1) {
    Flash::setMsg('danger', 'You must be signed in to submit a Timesheet.');
    header('Location: /signin');
    exit();
}

$formToken = trim((string) ($_POST['drvrtoken'] ?? ''));
$sessionToken = trim((string) ($_SESSION['drvr_token'] ?? ''));

if ($formToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $formToken)) {
    Flash::setMsg('danger', 'The Timesheet request expired. Please try again.');
    header('Location: /timesheet?danger=request+expired');
    exit();
}

$periodStart = trim((string) ($_POST['period_start'] ?? ''));
$periodEnd = trim((string) ($_POST['period_end'] ?? ''));

if ($periodStart === '' || $periodEnd === '') {
    Flash::setMsg('danger', 'The Timesheet period is missing.');
    header('Location: /timesheet?danger=invalid+period');
    exit();
}

try {
    $pdo = (new Database())->connect();

    /*
     * Every repository participating in finalization receives the same
     * PDO connection so one transaction controls all database writes.
     */
    $assignmentRepository = new AssignmentRepository($pdo);
    $driverStatusRepository = new DriverStatusRepository($pdo);
    $timesheetRepository = new TimesheetRepository($pdo);
    $submissionRepository = new TimesheetSubmissionRepository($pdo);
    $userRepository = new UserRepository($pdo);

    $payPeriodService = new PayPeriodService();
    $timesheetService = new TimesheetService($timesheetRepository, $driverStatusRepository, $payPeriodService);
    $submissionService = new TimesheetSubmissionService($submissionRepository, $assignmentRepository, $timesheetService, $payPeriodService, $userRepository);
    $finalizationService = new TimesheetFinalizationService($pdo, $submissionService, $submissionRepository, $timesheetRepository, new TimesheetPdfRenderer(), new Storage());

    $submission = $finalizationService->submit($driverId, $periodStart, $periodEnd, 6, 'America/New_York');

    Flash::setMsg('success', 'Your Timesheet was submitted to payroll successfully.');
    header('Location: /timesheet?success=submitted&submission=' . (int) $submission['submission_id']);
    exit();
} catch (InvalidArgumentException | RuntimeException $e) {
    Flash::setMsg('danger', $e->getMessage());
    header('Location: /timesheet?danger=submission+failed');
    exit();
} catch (Throwable $e) {
    error_log('[TIMESHEET SUBMISSION] Final submission failed: ' . $e->getMessage());
    Flash::setMsg('danger', 'The Timesheet could not be submitted. Please try again.');
    header('Location: /timesheet?danger=submission+failed');
    exit();
}

?>