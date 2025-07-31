<?php

if (!isset($_SESSION['driver_id'])) {
    header("Location: /signup");
    die();
}

view('sendmail.view.php', ['title' => 'Contact Help',]);

?>