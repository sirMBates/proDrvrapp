<?php

declare(strict_types=1);

use Core\Flash;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$resetPending = $_SESSION['password_reset_pending'] ?? false;
$token = $_SESSION['password_reset_token'] ?? null;

if ($resetPending !== true || !is_string($token) || $token === '') {
    Flash::setMsg('danger', 'Your password reset session is invalid or has expired.');
    header("Location: /forget?danger=reset+expired");
    exit();
}

$formToken = (string) ($_POST['drvrtoken'] ?? '');
$sessionToken = (string) ($_SESSION['drvr_token'] ?? '');

if ($formToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $formToken)) {
    Flash::setMsg('danger', 'Please retry your request.');
    header("Location: /forget?danger=try+again");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset-pswd'])) {
    $password = (string) ($_POST['password'] ?? '');

    // Instantiate the reset token controller class. ↓    
    include_once base_path("app/SubmissionHandlers/completepasswordreset.php");

    $completePasswordReset = new CompletePasswordResetContr($token, $password);
    $completePasswordReset->completePasswordReset();

    unset($_SESSION['password_reset_pending'], $_SESSION['password_reset_token']);
    
    Flash::setMsg('success', 'Please log in to your account.');
    header("Location: /signin?success=reset+complete");
    exit();
}

?>