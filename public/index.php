<?php

require "../config.php";

define('BASE_PATH', __DIR__ . '/../');

define('HOME_PATH', __DIR__ . '/');

require BASE_PATH . 'core/Helperfunc.php';

require base_path('vendor/autoload.php');

$uri = parse_url($_SERVER['REQUEST_URI'])['path'];
// $method = isset($_POST['__method']) ? $_POST['__method'] : $_SERVER['REQUEST_METHOD'];
// Short hand method to code ⬆ is below ⬇
$method = $_POST['__method'] ?? $_SERVER['REQUEST_METHOD'];

if (isset($_SESSION['driver_id'])) {
    // Prevent logged-in users from visiting signup/signin
    if (in_array($uri, ['/signup','/signin','/forget','/compreset'])) {
        header("Location: /");
        exit();
    }
    $router = new \core\Router;
    $routes = require base_path("routes.php");
    $router->route($uri, $method);
    exit();
}

if ($uri === "/") {
    if (!isset($_COOKIE['driver_registered'])) {
        // New user → signup
        header("Location: /signup");
    } else {
        // Returning user → signin
        header("Location: /signin");
    }
    exit();
}
/*spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    require base_path("{$class}.php");
});*/

# To use old router file, use the path below this comment ⬇
# require base_path('core/router.php');

$router = new \core\Router;

$routes = require base_path("routes.php");

$router->route($uri, $method);

?>