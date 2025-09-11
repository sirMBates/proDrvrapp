<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['driver_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['error' => 'Direct access not allowed']);
    exit;
}

include_once base_path("app/models/getdrvrmodel.php");
include_once base_path("app/classes/get_drvr.php");
header('Content-Type: application/json');

$getDriversProfile = new GetDrvrContr();
$getDriversProfile->driverInfo();


?>