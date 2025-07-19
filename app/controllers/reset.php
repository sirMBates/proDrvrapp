<?php

use core\Flash;
// Get the token from the queryString using GET
$stringToken = $_GET['token'];
$token = hash('sha256', $stringToken);
//$tokenExpTime = date("Y-m-d H:i:s", time() + 60 * 30); // 30 minutes expire
include_once base_path("app/models/database.php");
include_once base_path("app/models/resetpwdmeth.php");
include_once base_path("app/classes/reset_pwd.php");
$resetValid = new ResetPwdContr($token);
$resetValid->isTokenExpired();
/*$alert = new Flash();
$alert::setMsg('success', 'Please fill out form below to complete the reset');
header("Location: /compreset?cleared=$token");
exit();*/

?>