<?php

$alert = new core\Flash();

if (session_status() !== 2) {
    session_start();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($_POST['__method'])) {
    $method = strtoupper($_POST['__method']);
}

$formToken = htmlspecialchars(trim($_POST['drvrtoken']));
if ($method === 'PATCH' && $formToken === $_SESSION['drvr_token']) {
    if (isset($_POST['reginfo'])) {
        // Getting the info from the form using POST method from the name attribute.
        $firstname = htmlspecialchars(trim($_POST['forename']));
        $lastname = htmlspecialchars(trim($_POST['surname']));
        $mobileNum = htmlspecialchars(trim($_POST['mobilenum']));
        $birthdate = htmlspecialchars(trim($_POST['dateofbirth']));
        $newCompanyId = htmlspecialchars(trim($_POST['operatorid']));
        // Instantiate the add user controller class. ↓
        include_once base_path("app/models/registrationmodel.php");
        include_once base_path("app/errors/complete_registration.php");
        $enterData = new RegistrationContr($newCompanyId, $firstname, $lastname, $mobileNum, $birthdate);
        $enterData->processProfile();
        setcookie(
            'driver_registered', 
            'true', 
            time() + (86400 * 365), // 1 year
            '/',                    // path
            'prodriver.local',      // domain
            true,                   // secure ( works with HTTPS )
            true                    // httponly
        );
        unset($_SESSION['driver_id']);
        // Go to signin page after firstname, lastname, mobile and birthdate has been successfully entered. ↓
        $alert::setMsg('success', 'You\'ve updated your profile successfully! Please sign in to continue.');
        header("Location: /signin?success=profile+updated");
        exit();
    }
} else {
    $alert::setMsg('danger', 'Please retry your request.');
    header("Location: /signup?danger=try+again");
    exit();
};
?>