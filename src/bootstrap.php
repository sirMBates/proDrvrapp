<?php

declare(strict_types=1);

define('BASE_PATH', realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);

define('HOME_PATH', __DIR__ . DIRECTORY_SEPARATOR);

//require "../config.php";
require BASE_PATH . "config.php";

require BASE_PATH . 'core/Helperfunc.php';

require base_path('vendor/autoload.php');

if (php_sapi_name() === 'cli') {
    global $argv;

    // Simple arg parser (job=import style)
    $args = [];
    foreach ($argv as $arg) {
        if (strpos($arg, '=') !== false) {
            [$k, $v] = explode('=', $arg, 2);
            $args[$k] = $v;
        }
    }

    // Run job scripts
    if (($args['job'] ?? '') === 'import') {
        require BASE_PATH . 'app/repository/jobordermodel.php';
        exit;
    }

    if (($args['job'] ?? '') === 'assignments') {
        require BASE_PATH . 'app/models/assignmentmodel.php';
        exit;
    }

    // Add more jobs as needed
}

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