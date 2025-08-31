<?php

if (session_status() !== 2) {
    session_start();
}

header('Content-Type: application/json');
$tokenHeader = getallheaders();
$drvrHiddenToken = isset($tokenHeader['X-CSRF-Token']) ? htmlspecialchars($tokenHeader['X-CSRF-Token'], ENT_QUOTES) : null;
$requestUri = $_SERVER['REQUEST_URI'];

if ($drvrHiddenToken === $_SESSION['drvr_token']) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $requestUri === '/setstatus') {
        include_once base_path("app/models/updatestatusmodel.php");
        include_once base_path("app/classes/update_drvr_status.php");
        $data = json_decode(file_get_contents("php://input"), true);
        if ($data) {
            $drvrId = $_SESSION['driver_id'];
            $drvrStatus = $data['drvrStatus'];
            $isoTimeStamp = $data['drvrStamp'];
            $timeStringStamp = strtotime($isoTimeStamp);
            $drvrTimeStamp = date('Y-m-d H:i:s', $timeStringStamp);
            $statusUpdater = new UpdateDrvrStatusContr($drvrId, $drvrStatus, $drvrTimeStamp, $drvrHiddenToken);
            $statusUpdater->checkAndUpdateDrvrStatus();
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Status updated'
            ]);
            exit();
        }
    }
} else {
    header('Content-Type: application/json');
    http_response_code(419);
    echo json_encode([
        'status' => 'error',
        'message' => 'Page expired'
    ]);
    exit();
}
//error_log("CSRF Token from header: " . $drvrHiddenToken);
//error_log("Session Driver ID: " . ($_SESSION['driver_id'] ?? 'not set'));
//error_log("Session Token: " . ($_SESSION['drvr_token'] ?? 'not set'));
//error_log("Request URI: " . $_SERVER['REQUEST_URI']);
//error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
?>