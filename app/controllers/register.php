<?php

if (!isset($_SESSION['user_name']) {
    header("Location: /signup");
    die();
}

view('register.view.php', ['title' => 'Pro Driver - Register',]);

?>