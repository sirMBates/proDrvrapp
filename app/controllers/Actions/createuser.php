<?php

declare(strict_types=1);

use App\Services\AccountRegistrationService;
use Core\Flash;
use Core\Logger;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$formToken = isset($_POST['userToken']) ? trim((string) $_POST['userToken']) : '';
$sessionToken = (string) ($_SESSION['user_token'] ?? '');

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
$password = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirmPassword'] ?? '');
$accessKey = trim((string) ($_POST['access_key'] ?? ''));

try {
    $registrationService = new AccountRegistrationService();

    $account = $registrationService->createAccount($username, $email, $password, $confirmPassword, $accessKey);

    $_SESSION['user_id'] = $account['user_id'];
    $_SESSION['user_name'] = $account['username'];
    $_SESSION['registration_pending'] = true;
    $_SESSION['registration_role'] = $account['role'];

    Flash::setMsg('success', 'Account created successfully! Please complete your profile.');
    header("Location: /register?success=acct+created", true, 303);
    exit();
} catch (\InvalidArgumentException $exception) {
    Flash::setMsg('danger', $exception->getMessage());
    header("Location: /signup?danger=invalid+registration", true, 303);
    exit();
} catch (\Throwable $exception) {
    Logger::logException($exception, 'Account registration failed');
    Flash::setMsg('danger', 'Unable to create your account. Please try again.');
    header("Location: /signup?danger=registration+failed", true, 303);
    exit();
}

?>