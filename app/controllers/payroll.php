<?php

if (!isset($_SESSION['driver_id'])) {
    header("Location: /signup");
    die();
}

view('payroll.view.php', ['title' => 'Work week summary',]);

?>