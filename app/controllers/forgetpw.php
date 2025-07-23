<?php

$alert = new core\Flash();

if (session_status() !== 2) {
    session_start();
}

if (isset($_POST['forget-pswd'])) {
// Getting the EMAIL value from the form using POST method from the name attribute.
    $email = htmlspecialchars($_POST['email']);
    $createToken = bin2hex(random_bytes(16));
    $token = hash("sha256", $createToken);
    // The time() method gives you the current time in seconds.
    $token_expires = date('Y-m-d H:i:s', time() + 60 * 30); // 30 minutes expiration
    include_once base_path("app/models/database.php");
    include_once base_path("app/models/forgetpwdmeth.php");
    include_once base_path("app/classes/forget_pswd.php");
    $startReset = new ForgetPswdContr($token, $token_expires, $email);
    $startReset->addTokenAndExpireTime();
    $startReset->sendForgetEmail();
    //echo $createToken."<br>";
    //echo $token;
    $alert::setMsg('info', 'Email sent! Please check your inbox to complete the reset.');
    header("Location: /forget?info=email+sent");
    exit();
}
?>