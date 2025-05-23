<?php
session_start();
if (isset($_POST['createAccount'])) {
    // Getting the info from the form using POST method from the name attribute.
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    $firstname = 'blank';
    $lastname = 'blanked';
    $mobileNum = '0009998765';
    $birthdate = '2010-03-30';
    // Instantiate the add user controller class. ↓
    include_once base_path("app/models/database.php");
    include_once base_path("app/models/addusermeth.php");
    include_once base_path("app/classes/add_user.php");
    $signup = new AddDrvrContr($username, $email, $password, $firstname, $lastname, $mobileNum, $birthdate);
    // Running error handlers and user signup.
    $signup->addDriver();
    $_SESSION['username'] = $username;
    session_write_close();
    //dd($_SESSION['username']);
    // If the user is successfully added to the database, redirect to the register page.
    // Go to registration page after username, email and password has been successfully entered.
    header("Location: /register");
    exit();
}

?>