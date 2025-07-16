<?php

$alert = new core\Flash();

if (session_status() !== 2) {
    session_start();
}

if (isset($_POST['forget-pswd'])) {
// Getting the EMAIL value from the form using POST method from the name attribute.
    $email = htmlspecialchars($_POST['email']);
    $token = bin2hex(random_bytes(16));
    $token_hash = hash('sha256', $token);
    $tokenExpTime = date("Y-m-d H:i:s", time() + 60 * 30); // 30 minutes expiration time
    include_once base_path("app/models/database.php");
    include_once base_path("app/models/forgetpwdmeth.php");
    include_once base_path("app/classes/forget_pswd.php");
    $startReset = new ForgetPswdContr($token_hash, $tokenExpTime, $email);
    $startReset->checkEmailandAddTokenAndExpireTime();
    $startReset->sendForgetEmail();
    $alert::setMsg('info', 'Email sent! Please check your inbox to complete the reset.');
    header("Location: /forget?info=email+sent");
    exit();
}
?>