<?php

declare(strict_types=1);

use Core\Flash;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$method = strtoupper($_POST['__method'] ?? $_SERVER['REQUEST_METHOD']);

if ($method !== 'PATCH') {
    http_response_code(405);
    exit();
}

$driverId = (int) ($_SESSION['driver_id'] ?? 0);

if ($driverId < 1) {
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
include_once base_path("app/SubmissionHandlers/update_profile.php");

if ($action === 'update-password') {
    $password = (string) ($_POST['password'] ?? '');

    $updateProfile = new UpdateDrvrContr($driverId, $password, null, null);
    $updateProfile->changeDriverPassword();
    Flash::setMsg('info', 'You\'ve successfully updated your password!');
    header("Location: /profile?info=password+updated");
    exit();
}

if ($action === 'update-contact-information') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $phoneNumber = trim((string) ($_POST['mobile'] ?? ''));

    $updateProfile = new UpdateDrvrContr($driverId, null, $email !== '' ? $email : null, $phoneNumber !== '' ? $phoneNumber : null);
    $updateProfile->updateContactInformation();
    Flash::setMsg('success', 'Your information has been updated.');
    header("Location: /profile?success=data+saved");
    exit();
}

http_response_code(400);
Flash::setMsg('error', 'Invalid profile update request.');
header("Location: /profile?error=invalid+request");
exit();

?>