<?php

$router->get('/signup', 'app/controllers/front-controllers/signup.php');
$router->get('/register', 'app/controllers/front-controllers/register.php');
$router->get('/signin', 'app/controllers/front-controllers/signin.php');
$router->get('/forget', 'app/controllers/front-controllers/forget.php');
$router->get('/completereset', 'app/controllers/front-controllers/completereset.php');
$router->get('/reset', 'app/controllers/back-controllers/reset.php');
$router->get('/', 'app/controllers/front-controllers/index.php', true);
$router->get('/assignment', 'app/controllers/front-controllers/assignment.php', true);
$router->get('/profile', 'app/controllers/front-controllers/profile.php', true);
$router->get('/timesheet', 'app/controllers/front-controllers/timesheet.php', true);
$router->get('/contact', 'app/controllers/front-controllers/contact.php', true);
$router->get('/help', 'app/controllers/front-controllers/help.php', true);
$router->get('/logout', 'app/controllers/back-controllers/logout.php');
$router->get('/getprofile', 'app/api/getprofile.php');
$router->get('/getassignments', 'app/api/getassignments.php');

$router->post('/signup', 'app/controllers/back-controllers/adduser.php');
$router->post('/signin', 'app/controllers/back-controllers/login.php');
$router->post('/forget', 'app/controllers/back-controllers/forgetpw.php');
$router->post('/completereset', 'app/controllers/back-controllers/finishpwdprocess.php');
$router->post('/reset', 'app/controllers/back-controllers/reset.php');
$router->post('/setstatus', 'app/api/setstatus.php');
$router->post('/contact', 'app/controllers/back-controllers/contacthelp.php');

$router->patch('/register', 'app/controllers/back-controllers/registration.php');
$router->patch('/profile', 'app/controllers/back-controllers/updateprofileacct.php');
$router->patch('/setprofilepicture', 'app/api/setprofilepicture.php');
$router->patch('/assignmenthandler', 'app/api/assignmenthandler.php');

$router->delete('/assignmenthandler', 'app/api/assignmenthandler.php');