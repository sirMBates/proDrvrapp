<?php

requireLoginAjax();
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'There was a problem updating status.'
        ]);
        exit();
    }

    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody, true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request data.'
        ]);
        exit();
    }

    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($data['csrf_token'] ?? null);
    if (empty($csrfToken)) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'You\'re unauthorized for this action.'
        ]);
        exit();
    }

    $driverId = (int) ($_SESSION['user_id'] ?? 0);
     if ($driverId < 1) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid user session.'
        ]);
        exit();
     }

    $driverStatus = $data['drvrStatus'] ?? null;
    $isoTimeStamp = $data['drvrStamp'] ?? null;
    $timeStringStamp = $isoTimeStamp !== null ? strtotime($isoTimeStamp) : false;
    if ($timeStringStamp === false) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid status timestamp.'
        ]);
        exit();
    }

    $driverTimeStamp = date('Y-m-d H:i:s', $timeStringStamp);

    include_once base_path("app/models/updatestatusmodel.php");
    include_once base_path("app/SubmissionHandlers/update_drvr_status.php");

    $statusUpdater = new UpdateDrvrStatusContr($driverId, $driverStatus, $driverTimeStamp, $csrfToken);
    $result = $statusUpdater->checkAndUpdateDrvrStatus();

    echo json_encode($result);
    exit();
} catch (Throwable) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unexpected server error.'
    ]);
    exit();
}

?>