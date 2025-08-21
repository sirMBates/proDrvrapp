<?php

if (!isset($_SESSION['logged_in'])) {
    header("Location: /signin");
    die();
}

view('home.view.php', ['title' => 'Home',]);

?>