<?php

if (session_status() !== 2) {
    session_start();
}

include_once base_path("app/models/getdrvrmeth.php");
include_once base_path("app/classes/get_drvr.php");

$requestUri = $_SERVER['REQUEST_URI'];
if ($requestUri === '/getprofile') {
    $getDriversProfile = new GetDrvrController();
    $getDriversProfile->driverInfo();
} else {
    http_response_code(404);
    echo 'Not Found';
}

?>