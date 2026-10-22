<?php

declare(strict_types=1);

$resetPending = $_SESSION['password_reset_pending'] ?? false;
$resetToken = $_SESSION['password_reset_token'] ?? null;

if ($resetPending !== true || !is_string($resetToken) || $resetToken === '') {
    header("Location: /forget");
    exit();
}

view('reset-password.view.php', ['title' => 'Reset your password',]);

?>