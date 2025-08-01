<?php

if (!isset($_SESSION['logged_in']) && $_SESSION['logged_in'] !== true) {
    header("Location: /signin");
    die();
}

view('sendmail.view.php', ['title' => 'Contact Help',]);

?>