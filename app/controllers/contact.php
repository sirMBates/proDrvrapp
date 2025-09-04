<?php

if (!isset($_SESSION['logged_in'])) {
    header("Location: /signin");
    exit();
}

view('sendmail.view.php', ['title' => 'Contact Help',]);

?>