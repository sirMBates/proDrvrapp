<?php

$alert = new core\Flash();

if (session_status() !== 2) {
    session_start();
}

$formToken = htmlspecialchars(trim($_POST['drvrtoken']));
if ($formToken === $_SESSION['drvr_token']) {
    if (isset($_POST['createAccount'])) {
        // Getting the info from the form using POST method from the name attribute.
        $username = htmlspecialchars(trim($_POST['username']));
        $email = htmlspecialchars(trim($_POST['email']));
        $password = htmlspecialchars(trim($_POST['password']));
        // Instantiate the add user controller class. ↓
        include_once base_path("app/models/addusermodel.php");
        include_once base_path("app/errors/add_user.php");
        $signup = new AddDrvrContr($username, $email, $password);
        // Running error handlers and user signup.
        $signup->addDriver();
        $_SESSION['user_name'] = $username;
        //dd($_SESSION['username']);
        // If the user is successfully added to the database (username, email and password has been entered), redirect to the register page with success alert.
        $alert::setMsg('success', 'Account created successfully! Please complete your profile.');
        header("Location: /register?success=acct+created", true, 303);
        exit();
    }
} else {
    $alert::setMsg('danger', 'Please retry your request.');
    header("Location: /signup?danger=try+again");
    exit();
}
?>