<?php

declare(strict_types=1);

use App\Repositories\TimesheetSubmissionRepository;
use App\Services\TimesheetHistoryService;
use Core\Database;
use Core\Storage;

$driverId = (int) ($_SESSION['user_id'] ?? 0);

if ($driverId < 1) {
    http_response_code(401);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Authentication is required.';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Method not allowed.';
    exit();
}

$submissionId = filter_var($_GET['submission_id'] ?? null, FILTER_VALIDATE_INT);

$disposition = strtolower(trim((string) ($_GET['disposition'] ?? 'inline')));

if ($submissionId === false || $submissionId < 1) {
    http_response_code(422);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Invalid Timesheet submission.';
    exit();
}

if (!in_array($disposition, ['inline', 'download'], true)) {
    http_response_code(422);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Invalid PDF disposition.';
    exit();
}

try {
    $pdo = (new Database())->connect();

    $submissionRepository = new TimesheetSubmissionRepository($pdo);

    $historyService = new TimesheetHistoryService($submissionRepository);

    $submission = $historyService->getDriverSubmission((int) $submissionId, $driverId);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    header('Content-Type: text/plain; charset=utf-8');
    echo $e->getMessage();
    exit();
} catch (RuntimeException $e) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'The requested Timesheet was not found.';
    exit();
} catch (Throwable $e) {
    error_log('[TIMESHEET PDF] Failed loading submission: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'The Timesheet PDF could not be loaded.';
    exit();
}

$pdfPath = trim((string) ($submission['pdf_path'] ?? ''));
$pdfHash = strtolower(trim((string) ($submission['pdf_sha256'] ?? '')));

if ($pdfPath === '' || preg_match('/^[a-f0-9]{64}$/', $pdfHash) !== 1) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'The Timesheet PDF is unavailable.';
    exit();
}

try {
    $storage = new Storage();
    $pdfBytes = $storage->readTimesheetPdf($pdfPath, $pdfHash);
} catch (Throwable $e) {
    error_log('[TIMESHEET PDF] Failed reading stored PDF: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'The Timesheet PDF could not be read.';
    exit();
}

$fileName = 'prodriver-timesheet-' . $submission['period_start'] . '-to-' . $submission['period_end'] . '.pdf';
$headerDisposition = $disposition === 'download' ? 'attachment' : 'inline';

header('Content-Type: application/pdf');
header('Content-Length: ' . strlen($pdfBytes));
header('Content-Disposition: ' . $headerDisposition . '; filename="' . $fileName . '"');
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');

echo $pdfBytes;
exit();

?>