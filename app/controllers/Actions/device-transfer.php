<?php

declare(strict_types=1);

use App\Services\UserSessionService;
use App\Services\AuthenticationService;
use Core\Flash;
use Core\Logger;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /signin', true, 303);
    exit();
}

// Validate the existing sign-in CSRF token.
$formToken = (string) ($_POST['userToken'] ?? '');
$sessionToken = (string) ($_SESSION['user_token'] ?? '');

if ($formToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $formToken)) {
    Flash::setMsg('danger', 'Please retry your request.');
    header('Location: /signin', true, 303);
    exit();
}

// A device transfer must originate from an unauthenticated session.
if (isset($_SESSION['user_id']) || ($_SESSION['logged_in'] ?? false) === true) {
    unset($_SESSION['pending_device_transfer']);

    Flash::setMsg('danger', 'Please sign out before switching devices.');
    header('Location: /signin', true, 303);
    exit();
}

$action = (string) ($_POST['transfer_action'] ?? '');

if (!in_array($action, ['confirm', 'cancel'], true)) {
    Flash::setMsg('danger', 'Invalid device-transfer action.');
    header('Location: /signin', true, 303);
    exit();
}

$pendingTransfer = $_SESSION['pending_device_transfer'] ?? null;

if (!is_array($pendingTransfer)) {
    Flash::setMsg('danger', 'Your device-transfer request is no longer available.');
    header('Location: /signin', true, 303);
    exit();
}

try {
    $sessionService = new UserSessionService();

    $userId = $sessionService->validatePendingTransfer($pendingTransfer);

    // Consume the pending request before proceeding.
    unset($_SESSION['pending_device_transfer']);

    if ($action === 'cancel') {
        Flash::setMsg('info', 'Device transfer cancelled. Your previous device remains signed in.');
        header('Location: /signin', true, 303);
        exit();
    }

    // Retrieve trusted account information from the server.
    $authenticationService = new AuthenticationService();
    $account = $authenticationService->findActiveDriverById($userId);

    // Rotate the pending authentication session ID.
    if (!session_regenerate_id(true)) {
        throw new \RuntimeException('Unable to regenerate the session ID.');
    }

    unset(
        $_SESSION['registration_pending'],
        $_SESSION['registration_role'],
        $_SESSION['user_name'],
        $_SESSION['birth_date']
    );

    // Atomically revoke previous sessions and authorize this one.
    $authorized = $sessionService->authorizeDriverSession($userId, true);

    if (!$authorized) {
        throw new \RuntimeException('Unable to authorize the new driver session.');
    }

    // Establish authenticated PHP session variables only after
    // the registry transaction has succeeded.
    $_SESSION['user_id'] = $account['userId'];
    $_SESSION['first_name'] = $account['firstName'];
    $_SESSION['logged_in'] = true;
    $_SESSION['role'] = $account['role'];

    unset(
        $_SESSION['registration_pending'],
        $_SESSION['registration_role'],
        $_SESSION['birth_date']
    );

    // Preserve the existing birthday greeting behavior.
    $birthdate = $account['birthdate'] ?? null;

    if (!empty($birthdate) && date('md') === date('md', strtotime($birthdate))) {
        $_SESSION['birth_date'] = $birthdate;
    }

    Flash::setMsg('success', $_SESSION['first_name'], ['greet' => true]);

    // Preserve an eligible Messenger destination.
    $messengerDestination = $_SESSION['messenger_login_destination'] ?? null;

    $redirect = '/';

    if (is_string($messengerDestination) && preg_match('~\A/message-center(?:\?(?:conversation|recipient)=[1-9][0-9]*)?\z~', $messengerDestination) === 1) {
        $redirect = $messengerDestination;
    }

    unset($_SESSION['messenger_login_destination']);
    header('Location: ' . $redirect, true, 303);
    exit();
} catch (\DomainException $exception) {
    unset($_SESSION['pending_device_transfer']);
    Flash::setMsg('danger', $exception->getMessage());
    header('Location: /signin', true, 303);
    exit();
} catch (\Throwable $exception) {
    unset($_SESSION['pending_device_transfer']);
    Logger::logException($exception, 'Device-transfer request failed');
    Flash::setMsg('danger', 'Unable to process your device-transfer request.');
    header('Location: /signin', true, 303);
    exit();
}

?>