<?php

requireLoginAjax();
header('Content-Type: application/json');

try {
    $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    $sessionToken = $_SESSION['drvr_token'] ?? null;
    $drvrHiddenToken = isset($headerToken ? htmlspecialchars($headerToken, ENT_QUOTES)) : null;
    // --- Fallback: check JSON body for csrf_token if header is missing ---
    if (empty($drvrHiddenToken)) {
        $rawBodyProbe = file_get_contents('php://input');
        $dataProbe = json_decode($rawBodyProbe, true);
        if (isset($dataProbe['csrf_token'])) {
            $drvrHiddenToken = htmlspecialchars($dataProbe['csrf_token'], ENT_QUOTES);
        }
    }

    if ($drvrHiddenToken) {
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
    } else {
        http_response_code(401);
        echo json_encode([
            'status' => 'info',
            'message' => 'You\'re unathorized for this action.'
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
};

?>