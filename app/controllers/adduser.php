<?php

$alert = new \core\Flash;

if (session_status() !== 2) {
    session_start();
}

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
    //dd($_SESSION['username']);
    // If the user is successfully added to the database (username, email and password has been entered), redirect to the register page.
    $alert->setMsg('success', 'acct-created', 'Account created successfully! Please enter additional information to complete your profile.');
 // Redirect to register page with success message
    header("Location: /register?success=createdaccount");
    exit();
}
?>