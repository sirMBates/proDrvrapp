<?php

declare(strict_types=1);

use App\Services\MailService;
use App\Services\PasswordResetService;
use Core\Flash;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$formToken = (string) ($_POST['drvrtoken'] ?? '');
$sessionToken = (string) ($_SESSION['drvr_token'] ?? '');

if ($formToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $formToken)) {
    Flash::setMsg('danger', 'Please retry your request.');
    header('Location: /forget?danger=try+again');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['forget-pswd'])) {
    http_response_code(405);
    exit();
}

$email = trim((string) ($_POST['email'] ?? ''));

$passwordResetService = new PasswordResetService();

$validationError = $passwordResetService->validateResetEmail($email);

if ($validationError !== null) {
    $message = match ($validationError) {
        'empty' => 'Please fill in all required fields.',
        'invalid' => 'Please re-enter your email.',
        default => 'Please check your email address.'
    };

    Flash::setMsg('warning', $message);
    header('Location: /forget?warning=' . urlencode($validationError));
    exit();
}

try {
    $resetRequest = $passwordResetService->createResetRequest($email);

    if ($resetRequest !== null) {
        try {
            $mailService = new MailService();
            $mailService->sendPasswordReset($resetRequest['email'], $resetRequest['rawToken']);
        } catch (\Throwable $exception) {
            // Invalidate the token if delivery fails.
            try {
                $passwordResetService->invalidateResetRequest((int) $resetRequest['resetId']);
            } catch (\Throwable $invalidationException) {
                error_log('Password reset token invalidation failed: ' . $invalidationException->getMessage());
            }

            throw $exception;
        }
    }

    Flash::setMsg('info', 'If an account matches that email, a password reset link has been sent.');
    header('Location: /forget?info=email+sent');
    exit();
} catch (\Throwable $exception) {
    error_log('Password reset request failed: ' . $exception->getMessage());
    Flash::setMsg('danger', 'Your request could not be processed. Please try again later.');
    header('Location: /forget?danger=system+error');
    exit();
}

?>