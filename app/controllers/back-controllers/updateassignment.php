<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$alert = new core\Flash();
$logFilePath = 'D:/webapps/logs/job_export_master.log';
$devLogger = new core\Logger($logFilePath);

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
        include_once base_path("app/models/assignmenthandlermodel.php");
        include_once base_path("app/errors/check_assignment_details.php");
        $data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        $modification = new UpdateAssignmentDetailsContr($data);
        $result = $modification->modify();
        /*file_put_contents('D:/webapps/logs/updateassignment_debug.log', "[" . date('Y-m-d H:i:s') . "] RESULT:\n" . print_r($result, true) . "\nPOST:\n" . print_r($_POST, true) . "\n\n", FILE_APPEND);*/

        if ( ($result['status'] ?? '') === 'success' ) {
            $alert::setMsg('success', $result['message'] ?? 'Assignment updated successfully.');
            $orderId = urlencode( (string)($result['order_id'] ?? ($data['order_id'] ?? '')) );
            header("Location: /assignments?updated=1&order_id={$orderId}");
            exit();
        }

        $alert::setMsg('error', $result['message'] ?? 'Assignment update failed.');
        header("Location: /assignments?error=update+failed");
        exit();
    }
    elseif (isset($_POST['assignment-complete'])) {
        include_once base_path("app/models/assignmenthandlermodel.php");
        include_once base_path("app/errors/check_assignment_details.php");
        include_once base_path("app/repository/jobexportmodel.php");

        // Sanitize incoming POST data
        $data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        // Validate & check assignment details using existing error checker
        $jobValidator = new UpdateAssignmentDetailsContr($data);
        $errorCheck = $jobValidator->complete($data);
        if (isset($errorCheck['status']) && $errorCheck['status'] === 'error') {
            $alert::setMsg('error', $errorCheck['message']);
            header("Location: /assignments?error=db+update");
            exit();
        }

        $model = new UpdateAssignment();

        try {
            $assignmentForExcel = $model->getAssignmentForExcel($data);
        } catch (\Throwable $e) {
            $logger->error("[Endpoint] Failed to fetch assignment for Excel: " . $e->getMessage());
            $alert::setMsg('error', 'Could not fetch assignment for dispatch. Please try again.');
            header("Location: /assignments?error=fetch_failed");
            exit();
        }

        // Pass updated data to excel exporter
        try {
            $filePath = 'C:/Users/bates/OneDrive/Documents/testworkassignment.xlsx';
            $exporter = new AssignmentExporter($filePath, $devLogger);
            $exportSuccess = $exporter->assignmentSubmitted($data, $assignmentForExcel);
        } catch (\Throwable $e) {
            $devLogger->error("[Endpoint] Excel export failed: " . $e->getMessage());
            $alert::setMsg('error', 'Assignment was not submitted. Please try again.');
            header("Location: /assignments?error=submission+failed");
            exit();
        }

        try {
            $dbResult = $model->completeAssignment($data);
            if (isset($dbResult['status']) && $dbResult['status'] === 'error') {
                $alert::setMsg('error', $dbResult['message']);
                header("Location: /assignments?error=db_update_failed");
                exit();
            }
        } catch (\Throwable $e) {
            $devLogger->error("[Endpoint] Failed to mark assignment completed: " . $e->getMessage());
            $alert::setMsg('error', 'Could not mark assignment completed.');
            header("Location: /assignments?error=complete_failed");
            exit();
        }

        $dbMarkComplete = $model->completeAssignment($data, true);

        $devLogger->info('[Updated] Operation was executed.');
        $alert::setMsg('success', 'Assignment has been submitted to dispatch and marked as completed.');
        header("Location: /assignments?success=assignment_completed&completed=1&order_id=" . urlencode($data['order_id']));
        exit();
    }
}

?>