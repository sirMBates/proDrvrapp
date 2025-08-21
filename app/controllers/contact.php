<?php

if (!isset($_SESSION['logged_in'])) {
    header("Location: /signin");
    die();
}

view('sendmail.view.php', ['title' => 'Contact Help',]);

?>