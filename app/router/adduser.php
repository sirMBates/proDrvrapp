<?php
session_start();
if (isset($_POST['createAccount'])) {
    // Getting the info from the form using POST method from the name attribute.
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $firstname = 'blank';
    $lastname = 'blanked';
    $mobileNum = '0009998765';
    $birthdate = '2010-03-30';
    // Instantiate the add user controller class. ↓
    include_once "../models/database.php";
    include_once "../models/addusermeth.php";
    include_once "../controllers/add_user.php";
    $signup = new AddDrvrContr($username, $email, $password, $firstname, $lastname, $mobileNum, $birthdate);
    // Running error handlers and user signup.
    $signup->addDriver();
    $_SESSION['username'] = $username;
    // Go to complete signup page after username, email and password has been successfully entered.
    header("Location: ../../public/views/complete_signup.php?error=none&status=notcomplete");
    exit();
}

?>