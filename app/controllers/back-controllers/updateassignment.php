<?php

use core\Flash;
$alert = new Flash();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

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
        $storage = new core\Storage();  // Could also use the directory location i.e. 'D:/prodrvr/public/signatures/'
        $modification = new UpdateAssignmentDetailsContr($data, $storage);
        $result = $modification->modify();
        /*file_put_contents('D:/webapps/logs/updateassignment_debug.log', "[" . date('Y-m-d H:i:s') . "] RESULT:\n" . print_r($result, true) . "\nPOST:\n" . print_r($_POST, true) . "\n\n", FILE_APPEND);*/

        $alert::setMsg('success', 'Assignment updated successfully.');
        $orderId = urlencode( (string)($result['order_id'] ?? ($data['order_id'] ?? '')) );
        header("Location: /assignments?updated=1&order_id={$orderId}");
        exit();

        /*$alert::setMsg('error', $result['message'] ?? 'Assignment update failed.');
        header("Location: /assignments?error=update+failed");
        exit();*/
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

        $model = new UpdateAssignment();
        $assignmentForExcel = $model->getAssignmentForExcel($data);

        // Pass updated data to excel exporter
        $filePath = 'C:/Users/bates/OneDrive/Documents/testworkassignment.xlsx';
        $exporter = new AssignmentExporter($filePath, $devLogger);
        $exportSuccess = $exporter->assignmentSubmitted($data, $assignmentForExcel);
        $dbResult = $model->completeAssignmentPublic($data, true);

        $devLogger->info('[Updated] Operation was executed.');
        $alert::setMsg('success', 'Assignment submitted and marked as completed.');
        header("Location: /assignments?success=assignment_completed&completed=1&order_id=" . urlencode($data['order_id']));
        exit();
    }
}

?>