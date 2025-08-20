<?php

if (isset($_SESSION['driver_id'])) {
    header("Location: /");
    exit();
}

view('forget.view.php', ['title' => 'Forget your password',]);

?>