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
        include_once base_path("core/database.php");
        include_once base_path("app/models/updateprofilemeth.php");
        include_once base_path("app/classes/update_profile.php");
        $newDrvrPwd = new UpdateDrvrPwdContr($drvrid, $password);
        $newDrvrPwd->changeDrvrPwd();
        $alert::setMsg('success', 'You\'ve successfully updated your password!');
        header("Location: /profile?success=password+updated");
        exit();
    }/*
    elseif (isset($_POST['updateTelEmail'])) {
        $driver = htmlspecialchars(Trim($_SESSION['driver_id']));
        $drvrEmail = isset($_POST['email'])? htmlspecialchars(trim($_POST['email'])) : null;
        $drvrMobile = isset($_POST['mobile'])? htmlspecialchars(trim($_POST['mobile'])) : null;        
        include_once base_path("core/database.php");
        include_once base_path("app/models/updateprofilemeth.php");
        include_once base_path("app/classes/update_profile.php");
        $changeData = new 
    }*/
}
?>