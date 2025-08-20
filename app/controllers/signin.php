<?php

if (isset($_SESSION['driver_id'])) {
    header("Location: /");
    exit();
}

view('signin.view.php', ['title' => 'Pro Driver - Sign in',]);

?>