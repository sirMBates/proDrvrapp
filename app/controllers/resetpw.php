<?php

$alert = new \core\Flash();

if (session_status() !== 2) {
    session_start();
}

if (isset($_GET['token'])) {
    // Get the token from the queryString
    $token = $_GET['token'];
    $sanitizedToken = filter_var($token, FILTER_SANITIZE_STRING);
    $token_hash = hash("sha256", $sanitizedToken);
    include_once base_path("app/models/database.php");
    include_once base_path("app/models/resetpwdmeth.php");
    include_once base_path("app/classes/reset_pwd.php");
    $driverToken = new ResetPwdContr($token_hash);
    $driverToken->isTokenExpired();
    $alert::setMsg('validate', 'Please fill out the form below to complete password reset.');
    header("Location: /reset?validate=cleared");
    exit();
} elseif (isset($_POST['generate'])) {
            // Create new token to send to email of driver
            $token = bin2hex(random_bytes(16));
            $token_hash = hash('sha256', $token);
            $tokenExpTime = date("Y-m-d H:i:s", time() + 60 * 30); // 30 minutes expiration time
            // Instantiate the reset token controller class. ↓
            include_once base_path("app/models/database.php");
            include_once base_path("app/models/resetpwdmeth.php");
            include_once base_path("app/classes/reset_pwd.php");
            $newDriverToken = new ResetPwdContr($token_hash, $token);
            $newDriverToken->createNewToken();
            $alert::setMsg('info', 'Please check your inbox for reset link.');
            header("Location: /reset?info=sent+email");
            exit();
}