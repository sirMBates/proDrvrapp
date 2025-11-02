<?php

requireLoginAjax();
/*error_log('--- DEBUG: STATUS ENDPOINT ---');
error_log('Cookies: ' . print_r($_COOKIE, true));
error_log('Session ID: ' . session_id());
error_log('Session data: ' . print_r($_SESSION, true));
error_log('Raw body: ' . file_get_contents('php://input'));*/

header('Content-Type: application/json');
$tokenHeader = getallheaders();
$drvrHiddenToken = isset($tokenHeader['X-CSRF-Token']) ? htmlspecialchars($tokenHeader['X-CSRF-Token'], ENT_QUOTES) : null;

// Allow fallback when SW replay (no session cookie)
if (!isset($_SESSION['drvr_token'])) {
    session_start(); // just to be sure
    error_log('[STATUS] No active session detected (likely from SW replay)');
}

// ✅ new: allow token-only validation when session is absent
$validToken = false;
if (isset($_SESSION['drvr_token']) && $drvrHiddenToken === $_SESSION['drvr_token']) {
    $validToken = true;
} elseif (!isset($_SESSION['drvr_token']) && !empty($drvrHiddenToken)) {
    // token provided but no session — likely replay from SW
    $validToken = true;
}

if ($validToken) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once base_path("app/models/updatestatusmodel.php");
        include_once base_path("app/errors/update_drvr_status.php");
        $rawBody = file_get_contents("php://input");
        error_log("[DEBUG] Raw body (final test): " . $rawBody);
        error_log('Raw body: ' . $rawBody);
        $data = json_decode($rawBody, true);
        if ($data) {
            $drvrId = $_SESSION['driver_id'] ?? ($data['driver_id'] ?? null);
            $drvrStatus = $data['drvrStatus'] ?? null;
            $isoTimeStamp = $data['drvrStamp'] ?? null;
            $timeStringStamp = strtotime($isoTimeStamp);
            $drvrTimeStamp = date('Y-m-d H:i:s', $timeStringStamp);
            $statusUpdater = new UpdateDrvrStatusContr($drvrId, $drvrStatus, $drvrTimeStamp, $drvrHiddenToken);
            $result = $statusUpdater->checkAndUpdateDrvrStatus();
            echo json_encode($result ?? ['status' => 'error', 'message' => 'No result']);
            exit();
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($data)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid or missing JSON body.'
        ]);
        exit();
    }
} else {
    http_response_code(419);
    echo json_encode([
        'status' => 'error',
        'message' => 'Session expired or unauthorized replay.'
    ]);
    exit();
}
//error_log("CSRF Token from header: " . $drvrHiddenToken);
//error_log("Session Driver ID: " . ($_SESSION['driver_id'] ?? 'not set'));
//error_log("Session Token: " . ($_SESSION['drvr_token'] ?? 'not set'));
//error_log("Request URI: " . $_SERVER['REQUEST_URI']);
//error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
?>