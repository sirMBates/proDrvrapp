<?php

if (!isset($_SESSION['logged_in'])) {
    header("Location: /signin");
    die();
}

view('printable.view.php', ['title' => 'Pro Driver - WWS Sheet',]);

?>