<?php

use Core\Flash;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$alert = new Flash();
$formToken = trim((string) ($_POST['drvrtoken'] ?? ''));
$sessionToken = $_SESSION['drvr_token'] ?? '';

if ($formToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $formToken)) {
    $alert::setMsg('error', 'Unfortunately, there was an issue with your request. Please, try again.');
    header("Location: /contact?error=page+expired");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sendmsg'])) {
    $operatorId = trim((string) ($_POST['operatorid']));
    $driverName = trim((string) ($_POST['driverName']));
    $driverEmail = trim((string) ($_POST['driverEmail']));
    $helpDeskEmail = trim((string) ($_POST['helpDeskEmail']));
    $emailSubject = trim((string) ($_POST['subjectTitle']));
    $emailMessage = trim((string) ($_POST['message']));

    include_once base_path("app/SubmissionHandlers/contact_help.php");

    $sendingInfo = new ContactHelpContr((int) $_SESSION['driver_id'], $operatorId, $driverName, $driverEmail, $helpDeskEmail, $emailSubject, $emailMessage);
    $sendingInfo->contactHelpDesk();
    $alert::setMsg('info', 'Your message was sent. You\'ll receive a response shortly.');
    header("Location: /contact?info=message+sent");
    exit();
}

?>