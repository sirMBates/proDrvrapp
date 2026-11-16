<?php

$router->get('/', 'app/controllers/Pages/dispatch/index.php', true);
$router->get('/getdispatchprofile', 'app/api/getdispatchprofile.php');

?>