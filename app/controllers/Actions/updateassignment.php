<?php

declare(strict_types=1);

use Core\Database;
use App\Repositories\AssignmentRepository;
use App\Repositories\DriverSharedNoteRepository;
use App\Repositories\EmergencyRepository;
use App\Repositories\DriverStatusRepository;
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

            $emergencyService = new EmergencyService($pdo, $emergencyRepository, $driverStatusRepository);
            $assignmentService = new AssignmentService($assignmentRepository, $driverSharedNoteRepository, $emergencyService);

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
        include_once base_path("app/models/assignmenthandlermodel.php");
        include_once base_path("app/SubmissionHandlers/check_assignment_details.php");

        // Sanitize incoming POST data
        $data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];
        $storage = new Storage();

        // Validate & check assignment details using existing error checker
        $jobValidator = new UpdateAssignmentDetailsContr($data, $storage);
        $jobValidator->validateForCompletion($data, false);
        $jobValidator->modify();

        $model = new UpdateAssignment();
        $updatedAssignment = $model->getAssignmentForExcel($data);
        $jobValidator->verifySignaturesForCompletion($updatedAssignment);

        // Pass updated data to excel exporter
        $filePath = 'D:/Documents/TestAssignments.xlsx';
        $exporter = new AssignmentExporter($filePath, $devLogger);
        $exporter->assignmentSubmitted($data, $updatedAssignment);
        $completedAssignment = $model->completeAssignmentPublic($data, true);

        $devLogger->info('[ASSIGNMENT COMPLETE] Operation executed.');
        $alert::setMsg('success', 'Assignment completed and submitted.');
        $orderId = (string) ($completedAssignment['order_id'] ?? $updatedAssignment['order_id'] ?? $data['order_id'] ?? '');
        $orderRef = (string) ($completedAssignment['order_ref'] ?? $updatedAssignment['order_ref'] ?? '');
        $query = http_build_query([
            'status' => 'completed',
            'completed' => $orderId,
            'order_id' => $orderId,
            'order_ref' => $orderRef
        ]);
        header("Location: /assignments?{$query}");
        exit();
    }
};

?>