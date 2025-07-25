<?php

$alert = new \core\Flash();

if (session_status() !== 2) {
    session_start();
}
if (isset($_POST['reginfo'])) {
    // Getting the info from the form using POST method from the name attribute.
    $firstname = $_POST['forename'];
    $lastname = $_POST['surname'];
    $mobileNum = $_POST['mobilenum'];
    $birthdate = $_POST['dateofbirth'];
    $formToken = $_POST['drvrtoken'];
    // Instantiate the add user controller class. ↓
    include_once base_path("app/models/database.php");
    include_once base_path("app/models/regprofilemeth.php");
    include_once base_path("app/classes/completeregis.php");
    $enterData = new RegProContr($firstname, $lastname, $mobileNum, $birthdate, $formToken);
    $enterData->processProfile();
    // Go to signin page after firstname, lastname, mobile and birthdate has been successfully entered. ↓
    $alert::setMsg('success', 'You\'ve updated your profile successfully! Please sign in to continue.');
    header("Location: /signin?success=profile+updated");
    exit();
};
?>