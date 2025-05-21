<?php

define('BASE_PATH', __DIR__ . '/../');

define('HOME_PATH', __DIR__ . '/');

require BASE_PATH . 'core/helperfunc.php';

//require base_path('vendor/autoload.php');

spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    require base_path("{$class}.php");
});

//require base_path('core/advrouter.php');

$router = new \core\advrouter();

$routes = require base_path("routes.php");

$uri = parse_url($_SERVER['REQUEST_URI'])['path'];
//$method = isset($_POST['__method']) ? $_POST['__method'] : $_SERVER['REQUEST_METHOD'];
# Short hand method to code ⬆ is below ⬇
$method = $_POST['__method'] ?? $_SERVER['REQUEST_METHOD'];

$router->route($uri, $method);

?>