<?php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: https://prodriver.local");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: X-CSRF-Token, Content-Type, X-Requested-With");

requireLoginAjax();

$headers = getallheaders();
$headerToken = $headers['X-CSRF-Token'] ?? null;
$sessionToken = $_SESSION['drvr_token'];

if ($headerToken !== $sessionToken) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied' // Invalid CRSF Token
    ]);
    exit();
}

include_once base_path("app/models/workassignmentsmodel.php");
include_once base_path("app/errors/get_work.php");

$getOperatorJob = new GetWorkContr();
$getOperatorJob->workInformation();

?>