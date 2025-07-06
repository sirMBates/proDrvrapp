<?php
$alert = new \core\Flash();
if (session_status() === 2) {
    session_start();
}

session_unset();
session_destroy();

// Prevent caching issues
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// Redirect to login page with success message
$alert::setMsg('success', 'You have successfully logged out. See you next time!');
header("Location: /signin?success=logged+out&status=unofficial");
exit();


?>