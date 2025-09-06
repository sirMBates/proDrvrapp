<?php

if (!isset($_SESSION['logged_in'])) {
    header("Location: /signin");
    exit();
}

view('contact.view.php', ['title' => 'Contact Help',]);

?>