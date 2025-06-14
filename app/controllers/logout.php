<?php
$alert = new \core\Flash;
if (session_status() === 2) {
    session_start();
}

session_unset();
session_destroy();

// Prevent caching issues
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// Redirect to login page
header("Location: /signin?status=loggedout");
exit();


?>