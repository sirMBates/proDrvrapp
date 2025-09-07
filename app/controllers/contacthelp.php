<?php

$alert = new core\Flash();

if (session_status() !== 2) {
    session_start();
}

$formToken = htmlspecialchars(trim($_POST['drvrtoken']));
if ($formToken === $_SESSION['drvr_token']) {
    if (isset($_POST['sendmsg'])) {
        $senderName = htmlspecialchars(trim($_POST['sender_name']));
        $senderEmail = htmlspecialchars(trim($_POST['sender_email']));
        $receiverEmail = htmlspecialchars(trim($_POST['receiver']));
        $emailMessage = htmlspecialchars(trim($_POST['message']));
        include_once base_path("app/models/addusermodel.php");
    }
}

?>