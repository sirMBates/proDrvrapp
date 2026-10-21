<?php

$userId = (int) ($_SESSION['user_id'] ?? 0);
$registrationPending = $_SESSION['registration_pending'] ?? false;

if ($userId < 1 || $registrationPending !== true) {
    header("Location: /signup");
    exit();
}

view('register.view.php', ['title' => 'Pro Driver - Register',]);

?>