<?php

use core\Flash;

if (session_status() !== 2) {
    session_start();
}
// Get the token from the queryString using GET
$token = htmlspecialchars($_GET['token']);
$formToken = htmlspecialchars($_POST['drvrtoken']);
//$getToken = hash("sha256", $token);
include_once base_path("app/models/database.php");
include_once base_path("app/models/resetpwdmeth.php");
include_once base_path("app/classes/reset_pwd.php");
$isResetValid = new ResetPwdContr($token);
$isResetValid->isTokenExpired();
$alert = new Flash();
$alert::setMsg('success', 'Please fill out form below to complete the reset');
header("Location: /compreset?cleared=$token", true, 303);
exit();

?>