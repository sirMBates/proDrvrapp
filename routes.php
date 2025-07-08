<?php

$router->get('/', 'app/controllers/index.php');
$router->get('/register', 'app/controllers/register.php');
$router->get('/signin', 'app/controllers/signin.php');
$router->get('/home', 'app/controllers/home.php');
$router->get('/orders', 'app/controllers/orders.php');
$router->get('/profile', 'app/controllers/profile.php');
$router->get('/payroll', 'app/controllers/payroll.php');
$router->get('/reset', 'app/controllers/reset.php');
$router->get('/contact', 'app/controllers/contact.php');
$router->get('/logout', 'app/controllers/logout.php');

$router->post('/', 'app/controllers/adduser.php');
$router->post('/register', 'app/controllers/regprofile.php');
$router->post('/signin', 'app/controllers/login.php');
$router->post('/reset', 'app/controllers/resetpw.php');