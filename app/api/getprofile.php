<?php

/*header('Content-Type: application/json');
echo json_encode(getallheaders(), JSON_PRETTY_PRINT);
exit();*/
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: https://prodriver.local");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: X-CSRF-Token, Content-Type, X-Requested-With");

/*dd(getallHeaders(), true);
exit();*/

requireLoginAjax();
$headers = getallheaders();
$headerToken = $headers['X-CSRF-Token'];
$sessionToken = $_SESSION['drvr_token'];

if ($headerToken !== $sessionToken) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied' // Invalid CRSF Token
    ]);
    exit();
}

include_once base_path("app/models/getdrvrmodel.php");
include_once base_path("app/classes/get_drvr.php");

$getDriversProfile = new GetDrvrContr();
$getDriversProfile->driverInfo();

?>