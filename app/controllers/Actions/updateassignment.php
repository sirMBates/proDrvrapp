<?php

declare(strict_types=1);

use Core\Database;
use App\Repositories\AssignmentRepository;
use App\Repositories\DriverSharedNoteRepository;
use App\Repositories\EmergencyRepository;
use App\Repositories\DriverStatusRepository;
use App\Repositories\TimesheetRepository;
use App\Services\AssignmentService;
use App\Services\EmergencyService;
use App\Validation\AssignmentValidator;
use App\ImportExport\AssignmentExporter;
use Core\Flash;
use Core\Storage;
use Core\Logger;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$alert = new Flash();
$logFilePath = 'D:/webapps/logs/error.log';
$devLogger = new Logger($logFilePath);

$headerToken = $_POST['X-CSRF-Token'] ?? null;
$sessionToken = $_SESSION['drvr_token'] ?? null;

if ($sessionToken === null) {
    $alert::setMsg('error', 'Your session has expired. Please sign in again.');
    header("Location: /signin");
    exit();
}

if ($headerToken !== $sessionToken) {
    $alert::setMsg('error', 'Access denied due to invalid token.');
    header("Location: /assignments?error=csrf");
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($_POST['__method'])) {
    $method = strtoupper($_POST['__method']);
}

if ($method === 'PATCH') {
    if (isset($_POST['modify'])) {
        $data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];
        $orderId = filter_var($data['order_id'] ?? null, FILTER_VALIDATE_INT);
        $driverId = (int) ($_SESSION['user_id'] ?? 0);
        $assignmentControl = trim((string) ($data['assignment_control'] ?? ''));

        if ($orderId === false || $orderId < 1 || $driverId < 1 || $assignmentControl === '') {
            $alert::setMsg('error', 'Invalid assignment request.');
            header("Location: /assignments?error=invalid_assignment", true, 303);
            exit();
        }

        $orderRef = '';
        $storage = null;
        $signatureBackup = null;

        try {
            $pdo = (new Database())->connect();

            $assignmentRepository = new AssignmentRepository($pdo);
            $driverSharedNoteRepository = new DriverSharedNoteRepository($pdo);
            $emergencyRepository = new EmergencyRepository($pdo);
            $driverStatusRepository = new DriverStatusRepository($pdo);
            $timesheetRepository = new TimesheetRepository($pdo);

            $emergencyService = new EmergencyService($pdo, $emergencyRepository, $driverStatusRepository);
            $assignmentService = new AssignmentService($assignmentRepository, $driverSharedNoteRepository, $emergencyService, $timesheetRepository);

            $prepared = $assignmentService->prepareUpdate((int) $orderId, $driverId, $assignmentControl, $data);

            $assignment = $prepared['assignment'];
            $preparedData = $prepared['data'];
            $orderRef = (string) ($assignment['order_ref'] ?? '');
            $signatureRequired = AssignmentValidator::requiresSignature($assignment);
            $preSignature = trim((string) ($data['pre_signature_base64'] ?? ''));
            $postSignature = trim((string) ($data['post_signature_base64'] ?? ''));

            if ($preSignature !== '' && !AssignmentValidator::hasPreTripSignature($data)) {
                throw new InvalidArgumentException('The pre-trip signature is invalid.');
            }

            if ($postSignature !== '' && !AssignmentValidator::hasPostTripSignature($data)) {
                throw new InvalidArgumentException('The post-trip signature is invalid.');
            }

            if ($signatureRequired) {
                if ($preSignature !== '' || $postSignature !== '') {
                    $storage = new Storage();
                    $signatureBackup = $storage->createSignatureBackup((string) $orderId);
                    $signatureData = $storage->saveSignatures([
                        'order_id' => (int) $orderId,
                        'pre_signature_base64' => $preSignature,
                        'post_signature_base64' => $postSignature
                    ]);

                    forEach($signatureData as $key => $value) {
                        if ($value !== null) {
                            $preparedData[$key] = $value;
                        }
                    }
                }
            } else {
                $preparedData['signature_status'] = 'not-required';
            }

            $result = $assignmentService->updatePrepared((int) $orderId, $driverId, $assignmentControl, $preparedData);

            if ($storage !== null && $signatureBackup !== null) {
                $storage->discardSignatureBackup($signatureBackup);
                $signatureBackup = null;
            }

            $alert::setMsg('success', $result['message'] ?? 'Assignment updated successfully.');

            $resultData = $result['data'] ?? [];
            $resultOrderId = (string) ($resultData['order_id'] ?? $orderId);
            $orderRef = (string) ($resultData['order_ref'] ?? '');

            $query = http_build_query([
                'status' => 'saved',
                'order_id' => $resultOrderId,
                'order_ref' => $orderRef
            ]);

            header("Location: /assignments?{$query}", true, 303);
            exit();
        } catch (InvalidArgumentException $e) {
            if ($storage !== null && $signatureBackup !== null) {
                try {
                    $storage->rollbackSignatureBackup($signatureBackup);
                } catch (Throwable $rollbackError) {
                    // Log this below when structured logging is wired here.
                }
            }

            unset($_SESSION['flash']['success']);
            $alert::setMsg('error', $e->getMessage());
            $query = http_build_query([
                'error' => 'assignment_update',
                'order_id' => (string) $orderId,
                'order_ref' => $orderRef,
                'assignment_control' => $assignmentControl
            ]);

            header("Location: /assignments?{$query}", true, 303);
            exit();
        } catch (RuntimeException $e) {
            if ($storage !== null && $signatureBackup !== null) {
                try {
                    $storage->rollbackSignatureBackup($signatureBackup);
                } catch (Throwable $rollbackError) {
                    // Log this below when structured logging is wired here.
                }
            }

            unset($_SESSION['flash']['success']);
            $alert::setMsg('error', $e->getMessage());
            $query = http_build_query([
                'error' => 'assignment_update',
                'order_id' => (string) $orderId,
                'order_ref' => $orderRef,
                'assignment_control' => $assignmentControl
            ]);

            header("Location: /assignments?{$query}", true, 303);
            exit();

        } catch (Throwable $e) {
            if ($storage !== null && $signatureBackup !== null) {
                try {
                    $storage->rollbackSignatureBackup($signatureBackup);
                } catch (Throwable $rollbackError) {
                    // Log this below when structured logging is wired here.
                }
            }

            unset($_SESSION['flash']['success']);
            $alert::setMsg('error', 'Assignment could not be updated. Please try again.');
            $query = http_build_query([
                'error' => 'assignment_update',
                'order_id' => (string) $orderId,
                'order_ref' => $orderRef,
                'assignment_control' => $assignmentControl
            ]);

            header("Location: /assignments?{$query}", true, 303);
            exit();
        }        
    }
    elseif (isset($_POST['assignment-complete'])) {
        $data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];
        $orderId = filter_var($data['order_id'] ?? null, FILTER_VALIDATE_INT);
        $driverId = (int) ($_SESSION['user_id'] ?? 0);
        $assignmentControl = trim((string) ($data['assignment_control'] ?? ''));

        if ($orderId === false || $orderId < 1 || $driverId < 1 || $assignmentControl === '') {
            $alert::setMsg('danger', 'Invalid assignment completion request.');
            header('Location: /assignments');
            exit();
        }

        // Filesystem resources participating in completion.
        $storage = null;
        $signatureBackup = null;
        $filePath = 'D:/Documents/TestAssignments.xlsx';
        $workbookBackupPath = null;
        $workbookOriginallyExisted = false;

        $pdo = null;

        try {
            /*
            * COMPOSITION ROOT
            * Every repository participating in Complete receives the
            * same PDO connection so they can participate in one MySQL
            * transaction.
            */
            $pdo = (new Database())->connect();

            $assignmentRepository = new AssignmentRepository($pdo);
            $driverSharedNoteRepository = new DriverSharedNoteRepository($pdo);
            $timesheetRepository = new TimesheetRepository($pdo);
            $emergencyRepository = new EmergencyRepository($pdo);
            $driverStatusRepository = new DriverStatusRepository($pdo);

            $emergencyService = new EmergencyService($pdo, $emergencyRepository, $driverStatusRepository);
            $assignmentService = new AssignmentService($assignmentRepository, $driverSharedNoteRepository, $emergencyService, $timesheetRepository);

            // PREPARE / VALIDATE
            // No database or filesystem mutations happen here.
            $prepared = $assignmentService->prepareCompletion((int) $orderId, $driverId, $assignmentControl, $data);

            /*
            * SIGNATURE BACKUP
            * We only need a signature backup if the browser actually
            * submitted a new pre/post signature.
            */
            $signaturePayload = $prepared['signature_payload'] ?? [];
            $preSignature = trim((string) ($signaturePayload['pre_signature_base64'] ?? ''));
            $postSignature = trim((string) ($signaturePayload['post_signature_base64'] ?? ''));

            $storage = new Storage();
            if ($preSignature !== '' || $postSignature !== '') {
                $signatureBackup = $storage->createSignatureBackup((string) $orderId);
            }

            /*
            * EXCEL BACKUP
            * MySQL cannot roll an .xlsx file back, so preserve the
            * workbook before AssignmentExporter touches it.
            */
            $workbookOriginallyExisted = is_file($filePath);
            if ($workbookOriginallyExisted) {
                $workbookBackupPath = $filePath . '.completion-' . bin2hex(random_bytes(8)) . '.bak';

                if (!copy($filePath, $workbookBackupPath)) {
                    throw new RuntimeException('Unable to create a workbook backup.');
                }
            }
            /*
            * Exporter is created only after its source workbook has
            * been protected.
            */
            $exporter = new AssignmentExporter($filePath, $devLogger);
            /*
            * DATABASE TRANSACTION
            */
            if (!$pdo->beginTransaction()) {
                throw new RuntimeException('Unable to begin assignment completion transaction.');
            }

            /*
            * completePrepared() performs:
            *
            * - Emergency recheck
            * - signature persistence
            * - changed-field update
            * - shared-note update
            * - authoritative reload
            * - signature verification
            * - Excel export
            * - final Emergency recheck
            * - confirmed -> completed
            * - completed assignment reload
            * - Timesheet snapshot creation
            */
            $result = $assignmentService->completePrepared((int) $orderId, $driverId, $assignmentControl, $prepared, $storage, $exporter);

            // Nothing becomes permanent in MySQL until every database
            // operation above succeeds.
            $pdo->commit();

            // SUCCESS — DISCARD COMPENSATING BACKUP
            if ($signatureBackup !== null) {
                try {
                    $storage->discardSignatureBackup($signatureBackup);
                } catch (Throwable $cleanupError) {
                    $devLogger->error('[ASSIGNMENT COMPLETE] Unable to discard ' . 'signature backup: ' . $cleanupError->getMessage());
                }

                $signatureBackup = null;
            }

            if ($workbookBackupPath !== null && is_file($workbookBackupPath)) {
                if (!unlink($workbookBackupPath)) {
                    $devLogger->error('[ASSIGNMENT COMPLETE] Unable to remove ' . 'workbook backup: ' . $workbookBackupPath);
                }

                $workbookBackupPath = null;
            }

            // SUCCESS RESPONSE
            $devLogger->info('[ASSIGNMENT COMPLETE] Operation executed.', [
                    'order_id' => $orderId,
                    'driver_id' => $driverId,
                    'assignment_control' => $assignmentControl
                ]
            );

            $alert::setMsg('success', $result['message'] ?? 'Assignment completed and submitted.');

            $resultData = $result['data'] ?? [];
            $completedOrderId = (string) ($resultData['order_id'] ?? $orderId);
            $orderRef = (string) ($resultData['order_ref'] ?? '');

            $query = http_build_query([
                'status' => 'completed',
                'completed' => $completedOrderId,
                'order_id' => $completedOrderId,
                'order_ref' => $orderRef
            ]);

            header("Location: /assignments?{$query}");
            exit();
        } catch (InvalidArgumentException $e) {
            // DATABASE ROLLBACK
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            // EXCEL ROLLBACK
            if ($workbookBackupPath !== null && is_file($workbookBackupPath)) {
                if (!copy($workbookBackupPath, $filePath)) {
                    $devLogger->error('[ASSIGNMENT COMPLETE] Workbook rollback failed.');
                }

                @unlink($workbookBackupPath);
                $workbookBackupPath = null;

            } elseif (!$workbookOriginallyExisted && is_file($filePath)) {
                // Workbook did not exist before Complete but was created
                // before a later failure.
                @unlink($filePath);
            }

            // SIGNATURE ROLLBACK
            if ($storage !== null && $signatureBackup !== null) {
                try {
                    $storage->rollbackSignatureBackup($signatureBackup);
                } catch (Throwable $rollbackError) {
                    $devLogger->error('[ASSIGNMENT COMPLETE] Signature rollback failed: ' . $rollbackError->getMessage());
                }
            }

            $devLogger->warning('[ASSIGNMENT COMPLETE] Validation failed: ' . $e->getMessage());

            $alert::setMsg('danger', $e->getMessage());
            header('Location: /assignments');
            exit();
        } catch (Throwable $e) {
            // DATABASE ROLLBACK        
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            // EXCEL ROLLBACK        
            if ($workbookBackupPath !== null && is_file($workbookBackupPath)) {
                if (!copy($workbookBackupPath, $filePath)) {
                    $devLogger->error('[ASSIGNMENT COMPLETE] Workbook rollback failed.');
                }

                @unlink($workbookBackupPath);
                $workbookBackupPath = null;

            } elseif (!$workbookOriginallyExisted && is_file($filePath)) {
                @unlink($filePath);
            }

            // SIGNATURE ROLLBACK
            if ($storage !== null && $signatureBackup !== null) {
                try {
                    $storage->rollbackSignatureBackup($signatureBackup);
                } catch (Throwable $rollbackError) {
                    $devLogger->error('[ASSIGNMENT COMPLETE] Signature rollback failed: ' . $rollbackError->getMessage());
                }
            }

            $devLogger->error('[ASSIGNMENT COMPLETE] Operation failed: ' . $e->getMessage());

            $alert::setMsg('danger', 'Assignment could not be completed. Please try again.');
            header('Location: /assignments');
            exit();
        }
    }
};

?>