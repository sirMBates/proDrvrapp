<?php
/*return [
    '/' => 'app/controllers/index.php',
    '/register' => 'app/controllers/register.php',
    '/signin' => 'app/controllers/signin.php',
    '/home' => 'app/controllers/home.php',
    '/orders' => 'app/controllers/orders.php',
    '/payroll' => 'app/controllers/payroll.php',
    '/profile' => 'app/controllers/profile.php',
    '/reset' => 'app/controllers/reset.php',
    '/contact' => 'app/controllers/contact.php',
];*/


$router->get('/', 'app/controllers/index.php');
$router->get('/register', 'app/controllers/register.php');
$router->get('/signin', 'app/controllers/signin.php');
$router->get('/home', 'app/controllers/home.php');
$router->get('/orders', 'app/controllers/orders.php');
$router->get('/payroll', 'app/controllers/payroll.php');
$router->get('/profile', 'app/controllers/profile.php');
$router->get('/reset', 'app/controllers/reset.php');
$router->get('/contact', 'app/controllers/contact.php');