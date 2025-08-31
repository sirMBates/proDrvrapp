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
        $formToken = htmlspecialchars(trim($_POST['drvrtoken']));
        // Instantiate the add user controller class. ↓
        include_once base_path("app/models/regprofilemodel.php");
        include_once base_path("app/classes/completeregis.php");
        $enterData = new RegProContr($firstname, $lastname, $mobileNum, $birthdate, $formToken);
        $enterData->processProfile();
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