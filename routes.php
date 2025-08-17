<?php

$router->get('/signup', 'app/controllers/signup.php');
$router->get('/register', 'app/controllers/register.php');
$router->get('/signin', 'app/controllers/signin.php');
$router->get('/forget', 'app/controllers/forget.php');
$router->get('/compreset', 'app/controllers/compreset.php');
$router->get('/reset', 'app/controllers/reset.php');
$router->get('/', 'app/controllers/index.php');
$router->get('/orders', 'app/controllers/orders.php');
$router->get('/profile', 'app/controllers/profile.php');
$router->get('/timesheet', 'app/controllers/timesheet.php');
$router->get('/printable', 'app/controllers/printable.php');
$router->get('/contact', 'app/controllers/contact.php');
$router->get('/logout', 'app/controllers/logout.php');
$router->get('/getprofile', 'app/api/getprofile.php');

$router->post('/signup', 'app/controllers/adduser.php');
$router->post('/signin', 'app/controllers/login.php');
$router->post('/forget', 'app/controllers/forgetpw.php');
$router->post('/compreset', 'app/controllers/compresetproc.php');
$router->post('/reset', 'app/controllers/reset.php');
$router->post('/setstatus', 'app/api/setstatus.php');

$router->patch('/register', 'app/controllers/regprofile.php');
$router->patch('/profile', 'app/controllers/updateprofileacct.php');