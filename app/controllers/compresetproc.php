<?php

use core\Flash;

if (session_status() !== 2) {
    session_start();
}

if (isset($_POST['reset-pswd'])) {
    $alert = new Flash();
    // Create new token to send to email of driver
    $password = htmlspecialchars($_POST['password']);
    $token = htmlspecialchars($_POST['resetToken']);
    // Instantiate the reset token controller class. ↓
    include_once base_path("app/models/database.php");
    include_once base_path("app/models/comppwdmeth.php");
    include_once base_path("app/classes/comp_pwd_process.php");
    $createNewPwd = new CompleteResetContr($token, $password);
    $createNewPwd->isTokenCleared();
    $createNewPwd->changeDrvrPassword();
    /*$alert::setMsg('success', 'You\'ve completed the reset. Please log in to your account.');
    header("Location: /signin?success=reset+complete");
    exit();*/
}
?>