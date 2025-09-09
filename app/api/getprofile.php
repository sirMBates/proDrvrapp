<?php

if (session_status() !== 2) {
    session_start();
}

include_once base_path("app/models/getdrvrmodel.php");
include_once base_path("app/classes/get_drvr.php");

$requestUri = $_SERVER['REQUEST_URI'];
if ($requestUri === '/getprofile') {
    header('Content-Type: application/json');
    $getDriversProfile = new GetDrvrContr();
    $getDriversProfile->driverInfo();
} else {
    headers('Content-Type: application/json');
    http_response_code(404);
    echo 'Not Found';
}

?>