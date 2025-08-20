<?php

if (isset($_SESSION['driver_id'])) {
    header("Location: /");
    exit();
}

view('signup.view.php', ['title' => 'Pro Driver - Sign up',]);

?>