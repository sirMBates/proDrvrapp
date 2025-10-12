<?php

$alert = new core\Flash();

if (session_status() !== 2) {
    session_start();
}

if (!isset($_GET['token'])) {
    header("Location: /forget");
    exit();
}
// Get the token from the queryString using GET
$token = htmlspecialchars($_GET['token']);
$formToken = htmlspecialchars($_POST['drvrtoken']);
//$getToken = hash("sha256", $token);
include_once base_path("app/models/resetpwdmodel.php");
include_once base_path("app/errors/reset_pwd.php");
$isResetValid = new ResetPwdContr($token);
$isResetValid->isTokenExpired();
$alert::setMsg('success', 'Please fill out form below to complete the reset');
header("Location: /compreset?cleared=$token");
exit();

?>