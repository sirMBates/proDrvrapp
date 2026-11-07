<?php

declare(strict_types=1);

use App\Services\PasswordResetService;
use Core\Flash;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Clear any previous pending password-reset session.
unset($_SESSION['password_reset_pending'], $_SESSION['password_reset_token']);

// Only allow GET requests.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit();
}

// Retrieve the raw token from the reset link.
$token = trim((string) ($_GET['token'] ?? ''));

if ($token === '') {
    Flash::setMsg('danger', 'Your password reset link is missing or invalid.');
    header('Location: /forget', true, 303);
    exit();
}

// Validate the token using the existing service.
$passwordResetService = new PasswordResetService();

$resetRequest = $passwordResetService->validateToken($token);

if ($resetRequest === null) {
    Flash::setMsg('danger', 'This password reset link is invalid or has expired.');
    header('Location: /forget?error=invalid', true, 303);
    exit();
}

// Store the validated token for the password-reset form.
$_SESSION['password_reset_pending'] = true;
$_SESSION['password_reset_token'] = $token;

Flash::setMsg('success', 'Please fill out the form below to complete the reset.');
header('Location: /reset-password', true, 303);
exit();

?>