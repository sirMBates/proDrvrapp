<?php

/*dd($_POST);
exit();*/
$alert = new core\Flash();

if (session_status() !== 2) {
    session_start();
}

$formToken = htmlspecialchars(trim($_POST['drvrtoken']));
if ($formToken === $_SESSION['drvr_token']) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sendmsg'])) {
        $driverName = htmlspecialchars(trim($_POST['sender_name']));
        $driverEmail = htmlspecialchars(trim($_POST['sender_email']));
        $helpDeskEmail = htmlspecialchars(trim($_POST['receiver']));
        $emailSubject = htmlspecialchars(trim($_POST['mail_subject']));
        $emailMessage = trim($_POST['message']);
        include_once base_path("app/models/getdrvrmodel.php");
        include_once base_path("app/classes/contact_help.php");
        $sendingInfo = new ContactHelpContr($_SESSION['driver_id'], $driverName, $driverEmail, $helpDeskEmail, $emailSubject, $emailMessage);
        $sendingInfo->contactHelpDesk();
    }
}

?>