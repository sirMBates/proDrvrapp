<?php
session_start();
if (isset($_POST['saveinfo'])) {
    // Getting the info from the form using POST method from the name attribute.
    $firstname = $_POST['forename'];
    $lastname = $_POST['surname'];
    $mobileNum = $_POST['mobilenum'];
    $birthdate = $_POST['dateofbirth'];
    $formToken = $_POST['drvrtoken'];
    // Instantiate the add user controller class. ↓
    include_once "../models/database.php";
    include_once "../models/drvrprofilemeth.php";
    include_once "../controllers/completedrvrprofile.php";
    $enterData = new DrvrProfileContr($firstname, $lastname, $mobileNum, $birthdate, $formToken);
    $enterData->processDrvrProfile();
    // Go to signin page after firstname, lastname, mobile and birthdate has been successfully entered. ↓
    header("Location: ../../public/views/drvrsignin.php?error=none&status=complete");
    exit();
};
?>