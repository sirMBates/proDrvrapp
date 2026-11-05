<?php

declare(strict_types=1);

use App\Services\AuthenticationService;
use Core\Flash;
use Core\Logger;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['loginAcct'])) {
    header('Location: /signin', true, 303);
    exit();
}

$formToken = trim((string) ($_POST['drvrtoken'] ?? ''));
$sessionToken = (string) ($_SESSION['drvr_token'] ?? '');

if ($formToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $formToken)) {
    Flash::setMsg('danger', 'Please retry your request.');
    header('Location: /signin?danger=try+again', true, 303);
    exit();
}

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

try {
    $authenticationService = new AuthenticationService();
    $account = $authenticationService->authenticate($username, $password);

    $messengerDestination = $_SESSION['messenger_login_destination'] ?? null;

    session_regenerate_id(true);

    $_SESSION['user_id'] = $account['userId'];
    $_SESSION['first_name'] = $account['firstName'];
    $_SESSION['logged_in'] = true;
    $_SESSION['role'] = $account['role'];

    unset($_SESSION['birth_date']);

    if ($_SESSION['role'] === 'driver') {
        $birthdate = $account['birthdate'] ?? null;

        if (!empty($birthdate) && date('md') === date('md', strtotime($birthdate))) {
            $_SESSION['birth_date'] = $birthdate;
        }
    }

    if (!isset($_COOKIE['account_registered'])) {
        setcookie('account_registered', 'true', [
            'expires' => time() + (86400 * 365),
            'path' => '/',
            'domain' => 'prodriver.local',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    Flash::setMsg('success', $_SESSION['first_name'], ['greet' => true]);
    $redirect = '/';

    if (is_string($messengerDestination) && preg_match('~\A/message-center(?:\?(?:conversation|recipient)=[1-9][0-9]*)?\z~', $messengerDestination) === 1) {
        $redirect = $messengerDestination;
    }

    unset($_SESSION['messenger_login_destination']);
    header('Location: ' . $redirect, true, 303);
    exit();
} catch (\InvalidArgumentException $exception) {
    Flash::setMsg('danger', $exception->getMessage());
    header('Location: /signin?danger=invalid+credentials', true, 303);
    exit();
} catch (\DomainException $exception) {
    Flash::setMsg('danger', $exception->getMessage());
    header('Location: /signin?danger=account+locked', true, 303);
    exit();
} catch (\Throwable $exception) {
    Logger::logException($exception, 'Account authentication failed');
    Flash::setMsg('danger', 'Unable to sign in. Please try again.');
    header('Location: /signin?danger=authentication+failed', true, 303);
    exit();
}

?>