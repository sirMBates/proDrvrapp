<?php
use Core\Flash;

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
}

session_destroy();
session_start();
session_regenerate_id(true);

// Store Logout success message in the new session.
Flash::setMsg('success', 'See you next time!');

// Prevent caching issues
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Redirect
header("Location: /signin?success=logged+out&status=unofficial", true, 303);
exit();

?>