<?php

$alert = new \core\Flash();

if (session_status() !== 2) {
    session_start();
}

if (isset($_POST['generate'])) {
    // Create new token to send to email of driver
    $newToken = bin2hex(random_bytes(16));
    $token = hash('sha256', $newToken);
    $tokenExpTime = date("Y-m-d H:i:s", time() + 60 * 30); // 30 minutes expire
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