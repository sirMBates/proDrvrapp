<?php

declare(strict_types=1);

use App\Services\AuthenticationService;
use App\Services\UserSessionService;
use Core\Flash;
use Core\Logger;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['loginAcct'])) {
    header('Location: /signin', true, 303);
    exit();
}

if (isset($_SESSION['user_id']) && ($_SESSION['logged_in'] ?? false) === true) {
    try {
        $sessionService = new UserSessionService();

        if ($sessionService->isCurrentValid((int) $_SESSION['user_id'])) {
            header('Location: /', true, 303);
            exit();
        }
    } catch (\Throwable $exception) {
        Logger::logException($exception, 'Unable to validate existing login session');
    }

    // Never carry stale authentication into a new login attempt.
    unset(
        $_SESSION['user_id'],
        $_SESSION['logged_in'],
        $_SESSION['first_name'],
        $_SESSION['role'],
        $_SESSION['birth_date']
    );
}

$formToken = trim((string) ($_POST['userToken'] ?? ''));
$sessionToken = (string) ($_SESSION['user_token'] ?? '');

if ($formToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $formToken)) {
    Flash::setMsg('danger', 'Please retry your request.');
    header('Location: /signin?danger=try+again', true, 303);
    exit();
}

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

unset($_SESSION['pending_device_transfer']);

try {
    $authenticationService = new AuthenticationService();
    $account = $authenticationService->authenticate($username, $password);

    if (($account['registrationPending'] ?? false) === true) {
        session_regenerate_id(true);

        unset($_SESSION['logged_in'], $_SESSION['first_name'], $_SESSION['role'], $_SESSION['birth_date'], $_SESSION['messenger_login_destination']);

        $_SESSION['user_id'] = $account['userId'];
        $_SESSION['user_name'] = $account['username'];
        $_SESSION['registration_pending'] = true;
        $_SESSION['registration_role'] = $account['role'];

        Flash::setMsg('info', 'Welcome back! Please complete your registration to activate your account.');
        header('Location: /register?info=resume+registration', true, 303);
        exit();
    }

    if (($account['authenticated'] ?? false) !== true) {
        throw new \RuntimeException('Unexpected authentication result.');
    }

    $messengerDestination = $_SESSION['messenger_login_destination'] ?? null;

    if ($account['role'] === 'driver' && (new \App\Services\UserSessionService())->hasActiveSession((int) $account['userId'])) {
        if (!session_regenerate_id(true)) {
            throw new \RuntimeException('Unable to regenerate the session ID.');
        }

        unset(
            $_SESSION['user_id'],
            $_SESSION['logged_in'],
            $_SESSION['first_name'],
            $_SESSION['role'],
            $_SESSION['registration_pending'],
            $_SESSION['registration_role']
        );

        $_SESSION['pending_device_transfer'] = [
            'user_id' => (int) $account['userId'],
            'created_at' => time(),
            'expires_at' => time() + 300,
        ];

        header('Location: /signin?device_transfer=required', true, 303);
        exit();
    }

    if (!session_regenerate_id(true)) {
        throw new \RuntimeException('Unable to regenerate the session ID.');
    }

    unset($_SESSION['registration_pending'], $_SESSION['registration_role']);

    $userSessionService = new UserSessionService();

    if ($account['role'] === 'driver') {
        $authorized = $userSessionService->authorizeDriverSession((int) $account['userId']);

        if (!$authorized) {
            // A concurrent login may have established another session
            // after our initial existing-session check.
            $_SESSION['pending_device_transfer'] = [
                'user_id' => (int) $account['userId'],
                'created_at' => time(),
                'expires_at' => time() + 300,
            ];

            header('Location: /signin?device_transfer=required', true, 303);
            exit();
        }
    } else {
        $userSessionService->register((int) $account['userId']);
    }

    // Only establish authenticated PHP session state after
    // successful registry authorization.
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