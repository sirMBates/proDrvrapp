<?php

$userId = (int) ($_SESSION['user_id'] ?? 0);
$registrationPending = $_SESSION['registration_pending'] ?? false;
$registrationRole = (string) ($_SESSION['registration_role'] ?? '');

$allowedRegistrationRoles = [
    'driver',
    'dispatch',
    'admin'
];

if ($userId < 1 || $registrationPending !== true || !in_array($registrationRole, $allowedRegistrationRoles, true)) {
    header("Location: /signup");
    exit();
}

view('auth/register.view.php', ['title' => 'Pro Driver - Register', 'registrationRole' => $registrationRole]);

?>