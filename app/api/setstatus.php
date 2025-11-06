<?php

requireLoginAjax();
header('Content-Type: application/json');

try {
    $allHeaders = getallheaders();
    $drvrHiddenToken = isset($allHeaders['X-CSRF-Token']) ? htmlspecialchars($allHeaders['X-CSRF-Token'], ENT_QUOTES) : null;
    // --- Fallback: check JSON body for csrf_token if header is missing ---
    if (empty($drvrHiddenToken)) {
        $rawBodyProbe = file_get_contents('php://input');
        $dataProbe = json_decode($rawBodyProbe, true);
        if (isset($dataProbe['csrf_token'])) {
            $drvrHiddenToken = htmlspecialchars($dataProbe['csrf_token'], ENT_QUOTES);
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {            
        include_once base_path("app/models/updatestatusmodel.php");
        include_once base_path("app/errors/update_drvr_status.php");

        $rawBody = file_get_contents("php://input");
        $data = json_decode($rawBody, true);

        $drvrId = $_SESSION['driver_id'] ?? ($data['driver_id'] ?? null);
        $drvrStatus = $data['drvrStatus'] ?? null;
        $isoTimeStamp = $data['drvrStamp'] ?? null;
        $timeStringStamp = strtotime($isoTimeStamp);
        $drvrTimeStamp = date('Y-m-d H:i:s', $timeStringStamp);

        $statusUpdater = new UpdateDrvrStatusContr($drvrId, $drvrStatus, $drvrTimeStamp, $drvrHiddenToken);
        $result = $statusUpdater->checkAndUpdateDrvrStatus();
        echo json_encode($result);
        exit();
    } else {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'There was a problem updating status.'
        ]);
        exit();
    }
} catch (Throwable) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unexpected server error.'
    ]);
    exit();
}
/*
error_log("[DEBUG] Including model...");
//error_log("CSRF Token from header: " . $drvrHiddenToken);
//error_log("Session Driver ID: " . ($_SESSION['driver_id'] ?? 'not set'));
//error_log("Session Token: " . ($_SESSION['drvr_token'] ?? 'not set'));
//error_log("Request URI: " . $_SERVER['REQUEST_URI']);
//error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
//requireLoginAjax();
/*error_log('--- DEBUG: STATUS ENDPOINT ---');
error_log('Cookies: ' . print_r($_COOKIE, true));
error_log('Session ID: ' . session_id());
error_log('Session data: ' . print_r($_SESSION, true));
error_log('Raw body: ' . file_get_contents('php://input'));
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/setstatus_error.log');
error_log('[DEBUG] Entered setstatus.php');
error_log('[DEBUG] Passed requireLoginAjax()');
error_log("[DEBUG] All headers received: " . json_encode($allHeaders));
error_log("[DEBUG] Token extracted: " . ($drvrHiddenToken ?? 'none'));
error_log("[DEBUG] Fallback token from JSON body: " . $drvrHiddenToken);
error_log("[DEBUG] No csrf_token found in JSON body either.");
error_log("[DEBUG] POST detected - including model files...");
if (!file_exists(base_path("app/models/updatestatusmodel.php"))) {
                error_log("[FATAL] Model file not found at path: " . base_path("app/models/updatestatusmodel.php"));
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Model file missing'
                ]);
                exit();
            }
error_log("[DEBUG] Model included OK.");
error_log("[DEBUG] Including controller...");
error_log("[DEBUG] Controller included OK.");
error_log("[DEBUG] Starting JSON decode...");
error_log("[DEBUG] Raw body received: " . substr($rawBody, 0, 200)); // limit log size
if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("[ERROR] JSON decode failed: " . json_last_error_msg());
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Bad JSON: ' . json_last_error_msg()
                ]);
                exit();
            }
if (!$data) {
                error_log("[ERROR] Empty or missing body data.");
                http_response_code(400);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Missing JSON body']);
                exit();
            }
error_log("[DEBUG] Update result: " . json_encode($result));
error_log("[DEBUG] Final parsed data -> ID: {$drvrId}, Status: {$drvrStatus}, Time: {$drvrTimeStamp}");
error_log("[FATAL] Include failed: " .$inner->getMessage());
http_response_code(500);
        echo json_encode([
                'status' => 'error',
                'message' => 'Include failed: ' . $inner->getMessage()
            ]);
            exit();
        }
error_log("[FATAL] Exception in setstatus.php: " . $e->getMessage());
*/


?>