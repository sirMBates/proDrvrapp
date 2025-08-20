<?php

if (!isset($_SESSION['user_name'])) {
    header("Location: /signup");
    exit();
}

if (isset($_SESSION['driver_id'])) {
    header("Location: /");
    exit();
}

view('register.view.php', ['title' => 'Pro Driver - Register',]);

?>