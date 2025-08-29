<?php
    session_start();
    session_regenerate_id();
    $current_session_id = session_id();
    $new_session_id = session_create_id();
    $expiration_time = time() + 1000;
    $token = bin2hex(random_bytes(32));
    $_SESSION['token'] = $token;
    $session_status = session_status();
    echo nl2br("Session status: " . $session_status . "\nNew session ID: ". $current_session_id ."\nYour token is: ". $token);
?>