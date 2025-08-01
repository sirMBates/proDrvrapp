<?php

if (!isset($_SESSION['logged_in']) && $_SESSION['logged_in'] !== true) {
    header("Location: /signin");
    die();
}

view('payroll.view.php', ['title' => 'Work week summary',]);

?>