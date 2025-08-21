<?php

if (!isset($_SESSION['logged_in'])) {
    header("Location: /signin");
    die();
}

view('job.view.php', ['title' => 'Job Orders',]);

?>