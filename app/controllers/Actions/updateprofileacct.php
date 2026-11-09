<?php

declare(strict_types=1);

use App\Services\ProfileAccountService;
use Core\Flash;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$method = strtoupper($_POST['__method'] ?? $_SERVER['REQUEST_METHOD']);

if ($method !== 'PATCH') {
    http_response_code(405);
    exit();
}

$userId = (int) ($_SESSION['user_id'] ?? 0);

if ($userId < 1) {
    Flash::setMsg('danger', 'You must be logged into your account for this change.');
    header("Location: /signin");
    exit();
}

$formToken = (string) ($_POST['drvrtoken'] ?? '');
$sessionToken = (string) ($_SESSION['drvr_token'] ?? '');

if ($formToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $formToken)) {
    Flash::setMsg('danger', 'Please retry your request.');
    header("Location: /profile?danger=try+again");
    exit();
}

$action = (string) ($_POST['action'] ?? '');

if ($action === 'update-password') {
    $currentPassword = (string) ($_POST['currentPassword'] ?? '');
    $newPassword = (string) ($_POST['newPassword'] ?? '');
    $confirmPassword = (string) ($_POST['confirmPassword'] ?? '');

    $profileAccountService = new ProfileAccountService();

    $result = $profileAccountService->changePassword($userId, $currentPassword, $newPassword, $confirmPassword);

    if (!$result['success']) {
        $redirects = [
            'unauthorized' => '/signin',
            'current_password_required' => '/profile?warning=current+password+required',
            'new_password_required' => '/profile?warning=new+password+required',
            'confirm_password_required' => '/profile?warning=confirm+password+required',
            'invalid_password' => '/profile?warning=invalid+password',
            'passwords_not_match' => '/profile?warning=passwords+not+match',
            'incorrect_password' => '/profile?warning=incorrect+password'
        ];

        $code = $result['code'];
        $redirect = $redirects[$code] ?? '/profile?error=try+again';

        Flash::setMsg($code === 'unauthorized' ? 'danger' : 'warning', $result['message']);
        header("Location: {$redirect}");
        exit();
    }

    Flash::setMsg('info', $result['message']);
    header("Location: /profile?info=password+updated");
    exit();
}

if ($action === 'update-contact-information') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $phoneNumber = trim((string) ($_POST['mobile'] ?? ''));

    $profileAccountService = new ProfileAccountService();

    $result = $profileAccountService->changeContactInformation($userId, $email !== '' ? $email : null, $phoneNumber !== '' ? $phoneNumber : null);

    if (!$result['success']) {
        $redirects = [
            'unauthorized'   => '/signin',
            'empty'          => '/profile?warning=empty',
            'invalid_email'  => '/profile?warning=invalid+email',
            'email_exists'   => '/profile?warning=email+exists',
            'invalid_number' => '/profile?warning=invalid+number',
            'update_failed'  => '/profile?error=try+again'
        ];

        $code = $result['code'];
        $redirect = $redirects[$code] ?? '/profile?error=try+again';

        $flashType = match ($code) {
            'unauthorized'   => 'danger',
            'update_failed'  => 'error',
            default          => 'warning'
        };

        Flash::setMsg($flashType, $result['message']);
        header("Location: {$redirect}");
        exit();
    }

    Flash::setMsg('success', $result['message']);
    header("Location: /profile?success=data+saved");
    exit();
}

http_response_code(400);
Flash::setMsg('error', 'Invalid profile update request.');
header("Location: /profile?error=invalid+request");
exit();

?>