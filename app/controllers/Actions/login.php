<?php

use Core\Flash;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['loginAcct'])) {
    header("Location: /signin");
    exit();
}

$sessionToken = $_SESSION['drvr_token'] ?? '';
$formToken = (string) ($_POST['drvrtoken'] ?? '');

if ($formToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $formToken)) {
    Flash::setMsg('error', 'Your session has expired. Please try again.');
    header("Location: /signin?error=csrf");
    exit();
}

if (isset($_POST['loginAcct'])) {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    
    // Instantiate the sign in user controller class. ↓
    include_once base_path("app/SubmissionHandlers/login_drvr.php");
    $signin = new LoginContr($username, $password);
    // Running error handlers and user signin.
    $signin->loginUser();
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
    Flash::setMsg('success', $_SESSION['first_name'], ['greet' => true]);
    header("Location: /");
    exit();
}

?>