<?php

if (!isset($_SESSION['logged_in'])) {
    header("Location: /signin");
    die();
}

view('timesheet.view.php', ['title' => 'Work week summary',]);

?>