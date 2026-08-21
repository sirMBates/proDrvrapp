<?php

declare(strict_types=1);

use Core\Flash;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$token = trim((string) ($_GET['token'] ?? ''));
if ($token === '') {
    header("Location: /forget");
    exit();
}

include_once base_path("app/SubmissionHandlers/reset_pwd.php");
$isResetValid = new ResetPwdContr($token);
$isResetValid->validateResetToken();
Flash::setMsg('success', 'Please fill out form below to complete the reset.');
header("Location: /reset-password?cleared=" . urlencode($token));
exit();

?>