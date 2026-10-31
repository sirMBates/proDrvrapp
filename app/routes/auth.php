<?php

$router->get('/signup', 'app/controllers/Pages/auth/signup.php');
$router->get('/register', 'app/controllers/Pages/auth/register.php');
$router->get('/signin', 'app/controllers/Pages/auth/signin.php');
$router->get('/forget', 'app/controllers/Pages/auth/forget.php');
$router->get('/reset-password', 'app/controllers/Pages/auth/reset-password.php');
$router->get('/reset', 'app/controllers/Actions/reset.php');

$router->post('/signup', 'app/controllers/Actions/adduser.php');
$router->post('/signin', 'app/controllers/Actions/login.php');
$router->post('/forget', 'app/controllers/Actions/forgetpw.php');
$router->post('/reset-password', 'app/controllers/Actions/finishpwdprocess.php');
$router->post('/reset', 'app/controllers/Actions/reset.php');
$router->post('/logout', 'app/controllers/Actions/logout.php');

$router->patch('/register', 'app/controllers/Actions/registration.php');

?>