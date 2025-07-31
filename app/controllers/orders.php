<?php

if (!isset($_SESSION['driver_id'])) {
    header("Location: /signup");
    die();
}

view('job.view.php', ['title' => 'Job Orders',]);

?>