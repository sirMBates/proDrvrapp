<?php

$router->get('/', 'app/controllers/Pages/dispatch/index.php', true);
$router->get('/getdispatchprofile', 'app/api/getdispatchprofile.php');
$router->get('/connection-check', 'app/api/connection-check.php');

?>