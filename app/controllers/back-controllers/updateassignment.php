<?php

use core\Flash;
use core\Storage;
use core\Logger;

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
        include_once base_path("app/models/assignmenthandlermodel.php");
        include_once base_path("app/errors/check_assignment_details.php");
        $data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];
        $storage = new Storage();  // Could also use the directory location i.e. 'D:/prodrvr/public/signatures/'
        $modification = new UpdateAssignmentDetailsContr($data, $storage);
        $result = $modification->modify();

        $alert::setMsg('success', 'Assignment updated successfully.');
        $orderId = (string) ($result['order_id'] ?? $data['order_id'] ?? '');
        $orderRef = (string) ($result['order_ref'] ?? '');
        $query = http_build_query([
            'status' => 'saved',
            'order_id' => $orderId,
            'order_ref' => $orderRef
        ]);
        header("Location: /assignments?{$query}");
        exit();
    }
    elseif (isset($_POST['assignment-complete'])) {
        include_once base_path("app/models/assignmenthandlermodel.php");
        include_once base_path("app/errors/check_assignment_details.php");
        include_once base_path("app/repository/jobexportmodel.php");

        // Sanitize incoming POST data
        $data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];
        $storage = new Storage();

        // Validate & check assignment details using existing error checker
        $jobValidator = new UpdateAssignmentDetailsContr($data, $storage);
        $jobValidator->complete($data, false);
        $jobValidator->modify();

        $model = new UpdateAssignment();
        $updatedAssignment = $model->getAssignmentForExcel($data);
        $jobValidator->verifySignaturesForCompletion($updatedAssignment);

        // Pass updated data to excel exporter
        $filePath = 'C:/Users/bates/OneDrive/Documents/testworkassignment.xlsx';
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