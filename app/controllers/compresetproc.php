<?php

$alert = new core\Flash();

if (session_status() !== 2) {
    session_start();
}

$formToken = htmlspecialchars(trim($_POST['drvrtoken']));
if ($formToken === $_SESSION['drvr_token']) {
    if (isset($_POST['reset-pswd'])) {
        // Create new token to send to email of driver
        $token = htmlspecialchars($_POST['resetToken']);
        $password = htmlspecialchars($_POST['password']);
        //echo $token;
        //echo $password;
        // Instantiate the reset token controller class. ↓
        include_once base_path("app/models/comppwdmodel.php");
        include_once base_path("app/classes/comp_pwd_process.php");
        $createNewPwd = new CompleteResetContr($token, $password);
        $createNewPwd->changeDrvrPassword();
        $createNewPwd->hasTokenCleared();
        $alert::setMsg('success', 'Please log in to your account.');
        header("Location: /signin?success=reset+complete");
        exit();
    }
} else {
    $alert::setMsg('danger', 'Please retry your request.');
    header("Location: /signup?danger=try+again");
    exit();
}
?>