<?php

$router->get('/signup', 'app/controllers/signup.php');
$router->get('/register', 'app/controllers/register.php');
$router->get('/signin', 'app/controllers/signin.php');
$router->get('/forget', 'app/controllers/forget.php');
$router->get('/completereset', 'app/controllers/completereset.php');
$router->get('/reset', 'app/controllers/reset.php');
$router->get('/', 'app/controllers/index.php', true);
$router->get('/orders', 'app/controllers/orders.php', true);
$router->get('/profile', 'app/controllers/profile.php', true);
$router->get('/timesheet', 'app/controllers/timesheet.php', true);
$router->get('/printable', 'app/controllers/printable.php', true);
$router->get('/contact', 'app/controllers/contact.php', true);
$router->get('/logout', 'app/controllers/logout.php');
$router->get('/getprofile', 'app/api/getprofile.php');

$router->post('/signup', 'app/controllers/adduser.php');
$router->post('/signin', 'app/controllers/login.php');
$router->post('/forget', 'app/controllers/forgetpw.php');
$router->post('/completereset', 'app/controllers/finishpwdprocess.php');
$router->post('/reset', 'app/controllers/reset.php');
$router->post('/setstatus', 'app/api/setstatus.php');
$router->post('/contact', 'app/controllers/contacthelp.php');

$router->patch('/register', 'app/controllers/registration.php');
$router->patch('/profile', 'app/controllers/updateprofileacct.php');
$router->patch('/setprofilepicture', 'app/api/setprofilepicture.php');