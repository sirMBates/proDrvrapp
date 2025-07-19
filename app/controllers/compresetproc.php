<?php

$alert = new \core\Flash();

if (session_status() !== 2) {
    session_start();
}

if (isset($_POST['reset-pswd'])) {
    // Create new token to send to email of driver
    $password = htmlspecialchars($_POST['password']);
    $token = $_POST['resetToken'];
    // Instantiate the reset token controller class. ↓
    include_once base_path("app/models/database.php");
    include_once base_path("app/models/resetpwdmeth.php");
    include_once base_path("app/classes/reset_pwd.php");
    $newDriverToken = new ResetPwdContr($token, $tokenExpTime);
    $newDriverToken->createNewToken();
    $alert = new Flash();
    $alert::setMsg('info', 'Please check your inbox for reset link.');
    header("Location: /reset?info=sent+email");
    exit();
}
?>