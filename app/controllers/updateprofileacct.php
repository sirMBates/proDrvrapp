<?php

$alert = new core\Flash();

if (session_status() !== 2) {
    session_start();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($_POST['__method'])) {
    $method = strtoupper($_POST['__method']);
}

if ($method === 'PATCH') {
    if (isset($_POST['updatepswd'])) {
        $drvrid = htmlspecialchars(trim($_SESSION['driver_id']));
        $password = htmlspecialchars(trim($_POST['password']));
        $email = htmlspecialchars(trim(null));
        $phoneNumber = htmlspecialchars(trim(null));
        include_once base_path("app/models/updateprofilemodel.php");
        include_once base_path("app/classes/update_profile.php");
        $newDrvrPwd = new UpdateDrvrContr($drvrid, $password, $email, $phoneNumber);
        $newDrvrPwd->changeDrvrPwd();
        $alert::setMsg('success', 'You\'ve successfully updated your password!');
        header("Location: /profile?success=password+updated");
        exit();
    }
    elseif (isset($_POST['updateinfo'])) {
        $driver = htmlspecialchars(Trim($_SESSION['driver_id']));
        $drvrPassword = null; 
        $drvrEmail = isset($_POST['email'])? htmlspecialchars(trim($_POST['email'])) : null;
        //$drvrBirthDate = isset($_POST['birthdate'])? htmlspecialchars(trim($_POST['birthdate'])) : null;
        $drvrMobile = isset($_POST['mobile'])? htmlspecialchars(trim($_POST['mobile'])) : null;
        include_once base_path("app/models/updateprofilemodel.php");
        include_once base_path("app/classes/update_profile.php");
        $changeData = new UpdateDrvrContr($driver, $drvrPassword, $drvrEmail, /*$drvrBirthDate,*/$drvrMobile);
        $changeData->changeDrvrData();
        $alert::setMsg('info', 'Your information has been updated.');
        header("Location: /profile?info=data+saved");
        exit();
    }
}
?>