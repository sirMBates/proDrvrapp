<?php

$alert = new \core\Flash();

if (session_status() !== 2) {
    session_start();
}

if (isset($_POST['reset-pswd'])) {
    // Create new token to send to email of driver
    $token = htmlspecialchars($_POST['resetToken']);
    $password = htmlspecialchars($_POST['password']);
    //echo $token;
    //echo $password;
    // Instantiate the reset token controller class. ↓
    include_once base_path("app/models/database.php");
    include_once base_path("app/models/comppwdmeth.php");
    include_once base_path("app/classes/comp_pwd_process.php");
    $createNewPwd = new CompleteResetContr($token, $password);
    $createNewPwd->changeDrvrPassword();
    $createNewPwd->hasTokenCleared();
    $alert::setMsg('success', 'Please log in to your account.');
    header("Location: /signin?success=reset+complete");
    exit();
}
?>