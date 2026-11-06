<?php

declare(strict_types=1);

use App\Services\RegistrationService;
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
if ($userId < 1 || ($_SESSION['registration_pending'] ?? false) !== true) {
    Flash::setMsg('danger', 'Your registration session is no longer valid. Please sign in to continue.');
    header("Location: /signin?danger=registration+session+expired", true, 303);
    exit();
}

$registrationRole = (string) ($_SESSION['registration_role'] ?? '');
if (!in_array($registrationRole, ['driver', 'dispatch', 'admin'], true)) {
    Flash::setMsg('danger', 'Your registration session is no longer valid. Please sign in to continue.');
    header("Location: /signin?danger=registration+session+expired", true, 303);
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
$cdlNumber = null;
$cdlState = null;
$cdlExpirationDate = null;
$dotMedExpirationDate = null;

if ($registrationRole === 'driver') {
    $cdlNumber = trim((string) ($_POST['cdl_driver_license'] ?? ''));
    $cdlState = strtoupper(trim((string) ($_POST['driver_license_state'] ?? '')));
    $cdlExpirationDate = trim((string) ($_POST['driver_license_expire'] ?? ''));
    $dotMedExpirationDate = trim((string) ($_POST['dot_medical_expire'] ?? ''));
}

// Complete the account registration profile. ↓
$registrationService = new RegistrationService();

try {
    $updated = $registrationService->completeRegistration($userId, $firstName, $lastName, $mobileNumber, $birthDate, $cdlNumber, $cdlState, $cdlExpirationDate, $dotMedExpirationDate);
} catch (\InvalidArgumentException $exception) {
    Flash::setMsg('warning', $exception->getMessage());
    header("Location: /register?warning=invalid+registration");
    exit();
}

if (!$updated) {
    Flash::setMsg('error', 'The registration could not be completed. Please try again.');
    header("Location: /register?error=try+again");
    exit();
}

setcookie(
    'account_registered', 
    'true', 
    time() + (86400 * 365), // 1 year
    '/',                    // path
    'prodriver.local',      // domain
    true,                   // secure ( works with HTTPS )
    true                    // httponly
);

unset($_SESSION['registration_pending'], $_SESSION['registration_role'], $_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['logged_in'], $_SESSION['role'], $_SESSION['first_name'], $_SESSION['birth_date']);

session_regenerate_id(true);

// Go to signin page after information has been successfully entered. ↓
Flash::setMsg('success', 'You\'ve updated your profile successfully! Please sign in to continue.');
header("Location: /signin?success=profile+updated");
exit();

?>