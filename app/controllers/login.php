<?php
$alert = new \core\Flash();

if (session_status() !== 2) {
    session_start();
}

if (isset($_POST['loginAcct'])) {
    // Getting the info from the form using POST method from the name attribute.
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    // Instantiate the sign in user controller class. ↓
    include_once base_path("app/models/database.php");
    include_once base_path("app/models/drvrloginmeth.php");
    include_once base_path("app/classes/drvrlogin.php");
    $signin = new Logincontr($username, $password);
    // Running error handlers and user signin.
    $signin->loginDriver();
    // Redirect to home page upon successful login with valid message.
    $alert::setMsg('success', 'Logged in. Hello there, '. $_SESSION['first_name'] . '!');
    header("Location: /home");
    exit();
}

?>