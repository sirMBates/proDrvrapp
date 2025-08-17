<?php

if (session_status() !== 2) {
    session_start();
}

$tokenHeader = getallheaders();
if (isset($tokenHeader['X-CSRF-Token'])) {
    $drvrHiddenToken = htmlspecialchars($tokenHeader, ENT_QUOTES);
    if (isset($_SESSION['driver_id']) && $drvrHiddenToken === $_SESSION['drvr_token']) {
        include_once base_path("app/models/updatestatusmeth.php");
        include_once base_path("app/classes/update_drvr_status.php");

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        if ($data) {
            $drvrStatus = $data['drvrStatus'];
            $timeStamp = $data['timeStamp'];
            $setDrvrCurrentStatus = new UpdateDrvrStatusContr($_SESSION['driver_id'], $drvrStatus, $timeStamp, $drvrHiddenToken);
            $setDrvrCurrentStatus->checkAndUpdateDrvrStatus();
            echo json_encode([
                'status' => 'success',
                'message' => 'Status updated.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid JSON received.'
            ])
        }
    }
}
?>