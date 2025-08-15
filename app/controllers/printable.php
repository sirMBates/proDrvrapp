<?php

if (!isset($_SESSION['logged_in']) && $_SESSION['logged_in'] !== true) {
    header("Location: /signin");
    die();
}

view('printable.view.php', ['title' => 'Pro Driver - WWS Sheet',]);

?>