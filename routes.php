<?php

$router->get('/signup', 'app/controllers/Pages/signup.php');
$router->get('/register', 'app/controllers/Pages/register.php');
$router->get('/signin', 'app/controllers/Pages/signin.php');
$router->get('/forget', 'app/controllers/Pages/forget.php');
$router->get('/reset-password', 'app/controllers/Pages/reset-password.php');
$router->get('/reset', 'app/controllers/Actions/reset.php');
$router->get('/', 'app/controllers/Pages/index.php', true);
$router->get('/assignments', 'app/controllers/Pages/assignments.php', true);
$router->get('/int_messages', 'app/controllers/Pages/int_messages.php', true);
$router->get('/timesheet', 'app/controllers/Pages/timesheet.php', true);
$router->get('/profile', 'app/controllers/Pages/profile.php', true);
$router->get('/setprofilepicture', 'app/api/setprofilepicture.php', true);
$router->get('/contact', 'app/controllers/Pages/contact.php', true);
$router->get('/faqs', 'app/controllers/Pages/faqs.php', true);
$router->get('/counter', 'app/controllers/Pages/counter.php', true);
$router->get('/logout', 'app/controllers/Actions/logout.php');
$router->get('/getprofile', 'app/api/getprofile.php');
$router->get('/getassignments', 'app/api/getassignments.php');

$router->post('/signup', 'app/controllers/Actions/adduser.php');
$router->post('/signin', 'app/controllers/Actions/login.php');
$router->post('/forget', 'app/controllers/Actions/forgetpw.php');
$router->post('/reset-password', 'app/controllers/Actions/finishpwdprocess.php');
$router->post('/reset', 'app/controllers/Actions/reset.php');
$router->post('/setstatus', 'app/api/setstatus.php');
$router->post('/contact', 'app/controllers/Actions/contacthelp.php');

$router->patch('/register', 'app/controllers/Actions/registration.php');
$router->patch('/profile', 'app/controllers/Actions/updateprofileacct.php');
$router->patch('/setprofilepicture', 'app/api/setprofilepicture.php', true);
$router->patch('/assignmenthandler', 'app/api/assignmenthandler.php');
$router->patch('/assignments', 'app/controllers/Actions/updateassignment.php');
