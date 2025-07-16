<?php

use core\Flash;
// Get the token from the queryString using GET
$stringToken = $_GET['token'];
$token = hash('sha256', $stringToken);
include_once base_path("app/models/database.php");
include_once base_path("app/models/resetpwdmeth.php");
include_once base_path("app/classes/reset_pwd.php");
$resetValid = new ResetPwdContr($token);
$resetValid->isTokenExpired();
$alert::setMsg('success', 'Please fill out form below to complete the reset');
header("Location: /compreset?success=cleared");
exit();

?>