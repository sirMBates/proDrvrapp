<?php

if (!isset($_SESSION['driver_id'])) {
    header("Location: /signup");
    die();
}

view('profile.view.php', ['title' => 'Your Profile',]);

?>