<?php

if (session_status() !== 2) {
    session_start();
}


$tokenHeader = getallheaders();
$drvrHiddenToken = isset($tokenHeader['X-CSRF-Token']) 
    ? htmlspecialchars($tokenHeader['X-CSRF-Token'], ENT_QUOTES) 
    : null;
error_log("CSRF Token from header: " . $drvrHiddenToken);

error_log("Session Driver ID: " . ($_SESSION['driver_id'] ?? 'not set'));
error_log("Session Token: " . ($_SESSION['drvr_token'] ?? 'not set'));


//$requestUri = $_SERVER['REQUEST_URI'];
//$tokenHeader = getallheaders();
//$drvrHiddenToken;
if (isset($tokenHeader['X-CSRF-Token'])) {
    $drvrHiddenToken = htmlspecialchars($tokenHeader['X-CSRF-Token'], ENT_QUOTES);
}

if ($drvrHiddenToken === $_SESSION['drvr_token']) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $requestUri === '/setstatus') {
        error_log("Request URI: " . $_SERVER['REQUEST_URI']);
        error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

        include_once base_path("app/models/updatestatusmeth.php");
        include_once base_path("app/classes/update_drvr_status.php");

        $rawStatusData = file_get_contents("php://input");
        error_log("Raw JSON Input: " . $rawStatusData);
        //$rawStatusData = file_get_contents("php://input");
        $data = json_decode($rawStatusData, true);

        if ($data) {
            $drvrId = $_SESSION['driver_id'];
            $drvrStatus = $data['drvrStatus'];
            $timeStamp = $data['timeStamp'];
            $statusUpdater = new UpdateDrvrStatusContr($drvrId, $drvrStatus, $timeStamp, $drvrHiddenToken);
            $statusUpdater->checkAndUpdateDrvrStatus();
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Status updated.'
            ]);
        } else {
            error_log("JSON Decode Error: " . json_last_error_msg());
            error_log("Incoming JSON: " . file_get_contents("php://input"));
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid JSON received.'
            ]);
        }
    }
}
?>