<?php

if (!isset($_SESSION['driver_id'])) {
    header("Location: /signup");
    die();
}

view('home.view.php', ['title' => 'Home',]);

?>