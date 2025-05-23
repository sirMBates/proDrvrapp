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
    include_once base_path("app/models/database.php");
    include_once base_path("app/models/regprofilemeth.php");
    include_once base_path("app/classes/completeregis.php");
    $enterData = new DrvrProfileContr($firstname, $lastname, $mobileNum, $birthdate, $formToken);
    $enterData->processDrvrProfile();
    // Go to signin page after firstname, lastname, mobile and birthdate has been successfully entered. ↓
    header("Location: /signin?error=none");
    exit();
};
?>