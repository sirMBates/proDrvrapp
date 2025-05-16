<?php

$uri = parse_url($_SERVER['REQUEST_URI'])['path'];

$routes = [
    '/' => 'controllers/signup.php',
    '/register' => 'controllers/register.php',
    '/signin' => 'controllers/signin.php',
    '/home' => 'controllers/home.php',
    '/orders' => 'controllers/orders.php',
    '/payroll' => 'controllers/payroll.php',
    '/profile' => 'controllers/profile.php',
    '/reset' => 'controllers/reset.php',
    '/contact' => 'controllers/contact.php',
];

function routeToController($uri, $routes) {
    if (array_key_exists($uri, $routes)) {
        require $routes[$uri];
    } else {
        abort();
    }
}

function abort($code = 404) {
    http_response_code($code);
    require 'views/404.php';
    exit();
}

routeToController($uri, $routes);
