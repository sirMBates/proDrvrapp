<?php

use core\Flash;
// Get the token from the queryString using GET
$token = $_GET['token'];
$stringToken = hash("sha256", $token);
//$tokenExpTime = date("Y-m-d H:i:s", time() + 60 * 30); // 30 minutes expire
include_once base_path("app/models/database.php");
include_once base_path("app/models/resetpwdmeth.php");
include_once base_path("app/classes/reset_pwd.php");
$isResetValid = new ResetPwdContr($stringToken);
$isResetValid->isTokenExpired();
//echo "This page is live.";
/*$alert = new Flash();
$alert::setMsg('success', 'Please fill out form below to complete the reset');
header("Location: /compreset?cleared=$token");
exit();*/

?>