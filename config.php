<?php
//date_default_timezone_set('America/New_York');
ini_set('date.timezone', 'America/New_York');
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('session.cookie_samesite', 'None');
ini_set('session.save_path', __DIR__ . '/tmp');
error_reporting(E_ALL);

session_set_cookie_params([
    //↓lifetime is set in seconds (1 hr).
    'lifetime' => 3600,
    'domain' => 'prodriver.local',
    'path' => '/',
    'secure' => true,
    'httponly' => true
]);

session_start();
# The following code basically resets the session_id every 1hr.
# if there is no time variable set in session[last_regeneration]
# regenerate session id and add current time to session[last_regeneration] variable.
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}
else {
    # Interval 60secs x 60mins = total secs: 3600secs or 1hr
    # if there is a time set in session[last_regeneration] variable ↓
    # if the current time( time() ) (-)minus the time variable saved in session[last_regeneration]
    # is greater or equal to ( >= ) an hour which is the interval variable ( $interval = 60secs * 60mins )
    # update/regenerate session id (true) and update time in session variable [last_regeneration]
    $interval = 60 * 60;
    if (time() - $_SESSION['last_regeneration'] >= $interval) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

# create a token to store with the user to validate that this is said user
# store inside the database along with user info
# update token dynamically after a specific time has passed.↓

function generateToken() {
    $token = bin2hex(random_bytes(32));
    return $token;
}

if (!isset($_SESSION['drvr_token'])){
    $getToken = generateToken();
    $_SESSION['drvr_token'] = $getToken;
    $_SESSION['token_time'] = time(); 
}
else {
    $expiration_time = 60 * 15;
    if (time() - $_SESSION['token_time'] >= $expiration_time){
        $newToken = generateToken();
        $_SESSION['drvr_token'] = $newToken;
        $_SESSION['token_time'] = time();
    }
}

//phpinfo();
?>