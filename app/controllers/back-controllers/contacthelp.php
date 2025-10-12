<?php

$alert = new core\Flash();

if (session_status() !== 2) {
    session_start();
}

$formToken = htmlspecialchars(trim($_POST['drvrtoken']));
if ($formToken === $_SESSION['drvr_token']) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sendmsg'])) {
        $operatorId = htmlspecialchars(trim($_POST['operatorid']));
        $driverName = htmlspecialchars(trim($_POST['driverName']));
        $driverEmail = htmlspecialchars(trim($_POST['driverEmail']));
        $helpDeskEmail = htmlspecialchars(trim($_POST['helpDeskEmail']));
        $emailSubject = htmlspecialchars(trim($_POST['subjectTitle']));
        $emailMessage = trim($_POST['message']);
        include_once base_path("app/models/getdrvrmodel.php");
        include_once base_path("app/errors/contact_help.php");
        $sendingInfo = new ContactHelpContr($_SESSION['driver_id'], $operatorId, $driverName, $driverEmail, $helpDeskEmail, $emailSubject, $emailMessage);
        $sendingInfo->contactHelpDesk();
        $alert::setMsg('info', 'Your message was sent. You\'ll receive a response shortly.');
        header("Location: /contact?info=message+sent");
        exit();
    }
} else {
    $alert::setMsg('error', 'Unfortunately, there was an issue with your request. Please, try again.');
    header("Location: /contact?error=page+expired");
    exit();
}

?>