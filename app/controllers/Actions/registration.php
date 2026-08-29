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

$userId = (int) ($_SESSION['user_id'] ?? 0);
if ($userId < 1) {
    Flash::setMsg('danger', 'Your registration session is no longer valid. Please start again.');
    header("Location: /signup?danger=session+expired");
    exit();
}

$formToken = (string) ($_POST['drvrtoken'] ?? '');
$sessionToken = (string) ($_SESSION['drvr_token'] ?? '');
if ($formToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $formToken)) {
    Flash::setMsg('danger', 'Please retry your request.');
    header("Location: /register?danger=try+again");
    exit();
}

$action = (string) ($_POST['action'] ?? '');
if ($action !== 'complete-registration') {
    Flash::setMsg('error', 'Invalid registration request.');
    header("Location: /register?error=invalid+request");
    exit();
}

// Getting the info from the form using POST method from the name attribute.
$firstName = trim((string) ($_POST['forename'] ?? ''));
$lastName = trim((string) ($_POST['surname'] ?? ''));
$mobileNumber = trim((string) ($_POST['mobilenum'] ?? ''));
$birthDate = trim((string) ($_POST['dateofbirth'] ?? ''));
$operatorId = 'PRODRVR-' . str_pad((string) $userId, 5, '0', STR_PAD_LEFT);

// Complete the driver's registration profile. ↓
include_once base_path("app/SubmissionHandlers/complete_registration.php");
$registration = new RegistrationContr($userId, $operatorId, $firstName, $lastName, $mobileNumber, $birthDate);
$registration->processProfile();

setcookie(
    'driver_registered', 
    'true', 
    time() + (86400 * 365), // 1 year
    '/',                    // path
    'prodriver.local',      // domain
    true,                   // secure ( works with HTTPS )
    true                    // httponly
);

unset($_SESSION['user_id']);
unset($_SESSION['user_name']);

// Go to signin page after firstname, lastname, mobile and birthdate has been successfully entered. ↓
Flash::setMsg('success', 'You\'ve updated your profile successfully! Please sign in to continue.');
header("Location: /signin?success=profile+updated");
exit();

?>