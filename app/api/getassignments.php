<?php

header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: https://prodriver.local");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: X-CSRF-Token, Content-Type, X-Requested-With");

requireLoginAjax();

$headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
$sessionToken = $_SESSION['drvr_token'] ?? null;

if (!$headerToken || !$sessionToken || !hash_equals($sessionToken, $headerToken)) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied' // Invalid CSRF Token
    ]);
    exit();
}

include_once base_path("app/SubmissionHandlers/get_work.php");

$getOperatorAssignment = new GetAssignmentContr();
$getOperatorAssignment->assignmentInformation();

?>