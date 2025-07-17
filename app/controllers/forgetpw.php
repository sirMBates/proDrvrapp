<?php

$alert = new core\Flash();

if (session_status() !== 2) {
    session_start();
}

if (isset($_POST['forget-pswd'])) {
// Getting the EMAIL value from the form using POST method from the name attribute.
    $email = htmlspecialchars($_POST['email']);
    $setToken = bin2hex(random_bytes(16));
    $token = hash('sha256', $setToken);
    $token_time_format = date('Y-m-d H:i:s');
    $token_expires = strtotime($token_time_format) + (60 * 30); // 30 minutes expiration
    $tokenExpTime = date("Y-m-d H:i:s", $token_expires);
    include_once base_path("app/models/database.php");
    include_once base_path("app/models/forgetpwdmeth.php");
    include_once base_path("app/classes/forget_pswd.php");
    $startReset = new ForgetPswdContr($token, $tokenExpTime, $email);
    $startReset->addTokenAndExpireTime();
    $startReset->sendForgetEmail();
    $alert::setMsg('info', 'Email sent! Please check your inbox to complete the reset.');
    header("Location: /forget?info=email+sent");
    exit();
}
?>