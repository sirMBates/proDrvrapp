<?php

if (!isset($_SESSION['logged_in'])) {
    header("Location: /signin");
    die();
}

view('profile.view.php', ['title' => 'Your Profile',]);

?>