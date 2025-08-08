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
        $drvrid = htmlspecialchars($_SESSION['driver_id']);
        $password = htmlspecialchars($_POST['password']);
        include_once base_path("core/database.php");
        include_once base_path("app/models/updateprofilemeth.php");
        include_once base_path("app/classes/update_profile.php");
        $newDrvrPwd = new UpdateDrvrPwdContr($drvrid, $password);
        $newDrvrPwd->changeDrvrPwd();
        $alert::setMsg('success', 'You\'ve successfully updated your password!');
        header("Location: /profile?success=password+updated");
        exit();
    }
}
?>