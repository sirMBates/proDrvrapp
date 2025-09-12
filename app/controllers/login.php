<?php
$alert = new core\Flash();

if (session_status() !== 2) {
    session_start();
}

if (isset($_POST['loginAcct'])) {
    // Getting the info from the form using POST method from the name attribute.
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $formToken = htmlspecialchars($_POST['drvrtoken']);
    // Instantiate the sign in user controller class. ↓
    include_once base_path("app/models/logindrvrmodel.php");
    include_once base_path("app/classes/login_drvr.php");
    $signin = new Logincontr($username, $password);
    // Running error handlers and user signin.
    $signin->loginDriver();
    if (!isset($_COOKIE['driver_registered'])) {
        setcookie(
            'driver_registered', 
            'true', 
            time() + (86400 * 365), // 1 year
            '/',                    // path
            'prodriver.local',      // domain
            true,                   // secure ( works with HTTPS )
            true                    // httponly
        );
    }
    // Redirect to home page upon successful login with valid message.
    $alert::setMsg('success', 'Hello there, '. $_SESSION['first_name'] . '!');
    header("Location: /");
    exit();
}

?>