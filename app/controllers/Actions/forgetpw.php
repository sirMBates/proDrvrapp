<?php

declare(strict_types=1);

use Core\Flash;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$formToken = (string) ($_POST['drvrtoken'] ?? '');
$sessionToken = (string) ($_SESSION['drvr_token'] ?? '');

if ($formToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $formToken)) {
    Flash::setMsg('danger', 'Please retry your request.');
    header("Location: /forget?danger=try+again");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forget-pswd'])) {
    //Getting the EMAIL value from the form using POST method from the name attribute.
    $email = trim((string) ($_POST['email'] ?? ''));
    
    include_once base_path("app/SubmissionHandlers/forget_pswd.php");
    $startReset = new ForgetPswdContr($email);
    $resetRequest = $startReset->requestReset();
    
    Flash::setMsg('info', 'If an account matches that email, a password reset link has been sent.');
    header("Location: /forget?info=email+sent");
    exit();
}

?>