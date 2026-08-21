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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset-pswd'])) {
    $token = (string) ($_POST['resetToken'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    // Instantiate the reset token controller class. ↓
    
    include_once base_path("app/SubmissionHandlers/completepasswordreset.php");
    $completePasswordReset = new CompletePasswordResetContr($token, $password);
    $completePasswordReset->completePasswordReset();
    
    Flash::setMsg('success', 'Please log in to your account.');
    header("Location: /signin?success=reset+complete");
    exit();
}

?>