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
    ini_set('session.save_path', __DIR__ . '/tmp');

    session_set_cookie_params([
        //↓lifetime is set in seconds (1 hr).
        'lifetime' => 3600,
        //'domain' => 'prodriver.local',
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['last_regeneration'])) {
        # if there is no time variable set in session[last_regeneration]
        # regenerate session id and add current time to session[last_regeneration] variable.
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    } else {
        # The following code basically resets the session_id every 1hr.
        # Interval 60secs x 60mins = total secs: 3600secs or 1hr
        # if there is a time set in session[last_regeneration] variable
        # if the current time( time() ) (-)minus the time variable saved in session[last_regeneration]
        # is greater or equal to ( >= ) an hour which is the interval variable ( $interval = 60secs * 60mins )
        # update/regenerate session id (true) and update time in session variable [last_regeneration]
        $interval = 60 * 60;
        if (time() - $_SESSION['last_regeneration'] >= $interval) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }

    if (!isset($_SESSION['drvr_token']) || !isset($_SESSION['token_time'])) {
        $_SESSION['drvr_token'] = generateToken();
        $_SESSION['token_time'] = time();
    } else {
        # create a token to store with the user to validate that this is said user
        # update token dynamically after a specific time has passed.↓
        $expirationTime = 60 * 30;
        if (time() - (int) $_SESSION['token_time'] >= $expirationTime) {
            $_SESSION['drvr_token'] = generateToken();
            $_SESSION['token_time'] = time();
        }
    }
}

//phpinfo();
?>