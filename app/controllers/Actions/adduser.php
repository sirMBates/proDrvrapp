<?php

use Core\Flash;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$formToken = isset($_POST['drvrtoken']) ? trim((string) $_POST['drvrtoken']) : '';
$sessionToken = $_SESSION['drvr_token'] ?? '';

if ($formToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $formToken)) {
    Flash::setMsg('danger', 'Please retry your request.');
    header("Location: /signup?danger=try+again");
    exit();
}

if (!isset($_POST['createAccount'])) {
    header("Location: /signup");
    exit();
}

$username = trim((string) ($_POST['username'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$password = trim((string) ($_POST['password'] ?? ''));

include_once base_path("app/SubmissionHandlers/add_user.php");
$signup = new AddDrvrContr($username, $email, $password);
$signup->addDriver();

$_SESSION['user_name'] = $username;
Flash::setMsg('success', 'Account created successfully! Please complete your profile.');
header("Location: /register?success=acct+created", true, 303);
exit();

?>