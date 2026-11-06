<?php

declare(strict_types=1);

use App\Repositories\UserRepository;

$userId = (int) ($_SESSION['user_id'] ?? 0);
$registrationPending = ($_SESSION['registration_pending'] ?? false) === true;
$registrationRole = (string) ($_SESSION['registration_role'] ?? '');

$allowedRegistrationRoles = ['driver', 'dispatch', 'admin'];

if ($userId < 1 || !$registrationPending || !in_array($registrationRole, $allowedRegistrationRoles, true)) {
    header('Location: /signup', true, 303);
    exit();
}

try {
    $userRepository = new UserRepository();
    $user = $userRepository->findById($userId);

    if ($user['account_status'] !== 'pending' || $user['role'] !== $registrationRole) {
        unset($_SESSION['registration_pending'], $_SESSION['registration_role'], $_SESSION['user_id'], $_SESSION['user_name']);
        header('Location: /signin', true, 303);
        exit();
    }
} catch (\RuntimeException $exception) {
    unset($_SESSION['registration_pending'], $_SESSION['registration_role'], $_SESSION['user_id'], $_SESSION['user_name']);
    header('Location: /signin', true, 303);
    exit();
}

view('auth/register.view.php', ['title' => 'Pro Driver - Register', 'registrationRole' => $registrationRole]);

?>