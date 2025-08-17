<?php

if (session_status() !== 2) {
    session_start();
}

$tokenHeader = getallheaders();
if (isset($tokenHeader['X-CSRF-Token'])) {
    $drvrHiddenToken = htmlspecialchars($tokenHeader);
    if (isset($_SESSION['driver_id']) && $drvrHiddenToken === $_SESSION['drvr_token']) {
        //
    }
}
?>