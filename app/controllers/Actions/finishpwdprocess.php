<?php

declare(strict_types=1);

use App\Services\PasswordResetService;
use Core\Flash;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['reset-pswd'])) {
    http_response_code(405);
    exit();
}

$resetPending = $_SESSION['password_reset_pending'] ?? false;
$token = $_SESSION['password_reset_token'] ?? null;

if ($resetPending !== true || !is_string($token) || $token === '') {
    Flash::setMsg('danger', 'Your password reset session is invalid or has expired.');
    header('Location: /forget?danger=reset+expired', true, 303);
    exit();
}

$formToken = (string) ($_POST['userToken'] ?? '');
$sessionToken = (string) ($_SESSION['user_token'] ?? '');

if ($formToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $formToken)) {
    Flash::setMsg('danger', 'Please retry your request.');
    header('Location: /forget?danger=try+again', true, 303);
    exit();
}

$password = (string) ($_POST['password'] ?? '');

$passwordResetService = new PasswordResetService();

$validationError = $passwordResetService->validateResetPassword($password);

if ($validationError !== null) {
    $message = match ($validationError) {
        'empty' => 'Please enter your new password.',
        'invalid' => 'Please re-type your password.',
        default => 'Please enter a valid password.'
    };

    Flash::setMsg('warning', $message);
    header('Location: /reset-password', true, 303);
    exit();
}

$completed = $passwordResetService->completePasswordReset($token, $password);

if (!$completed) {
    // The token may have expired or already been used.
    unset($_SESSION['password_reset_pending'], $_SESSION['password_reset_token']);

    Flash::setMsg('danger', 'This password reset is invalid, expired, or has already been used.');
    header('Location: /forget?error=reset+failed', true, 303);
    exit();
}

// Clear the pending reset session after success.
unset($_SESSION['password_reset_pending'], $_SESSION['password_reset_token']);

Flash::setMsg('success', 'Please log in to your account.');
header('Location: /signin?success=reset+complete', true, 303);
exit();

?>