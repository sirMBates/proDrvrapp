<?php

$router->get('/signature', 'app/controllers/Pages/public/signature.php');
$router->post('/signature', 'app/api/signature-submit.php');
$router->post('/logout', 'app/controllers/Actions/logout.php');

?>