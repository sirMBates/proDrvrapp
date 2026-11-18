<?php

declare(strict_types=1);

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__, '.local.env');
$dotenv->load();
$dotenv->required('SECRET_KEY')->notEmpty();

//date_default_timezone_set('America/New_York');
ini_set('date.timezone', 'America/New_York');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

$isCli = PHP_SAPI === 'cli';

function generateToken(): string {
    $token = bin2hex(random_bytes(32));
    return $token;
}

if (!$isCli) {
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.save_path', BASE_PATH . 'storage/sessions');
    ini_set('session.gc_maxlifetime', '28800');

    session_set_cookie_params([
        //↓lifetime is set in seconds (1 hr).
        'lifetime' => 0,
        //'domain' => 'prodriver.local',
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    /*
    * Generate one CSRF token per session.
    * The token remains valid for the lifetime of the session.
    */
    if (!isset($_SESSION['user_token']) || !is_string($_SESSION['user_token']) || $_SESSION['user_token'] === '') {
        $_SESSION['user_token'] = generateToken();
    }
}

//phpinfo();
?>