<?php

define('BASE_PATH', __DIR__ . '/../');

define('HOME_PATH', __DIR__ . '/');

require BASE_PATH . 'core/helperfunc.php';

spl_autoload_register(function ($class) {
    require base_path('core/' . $class . 'php');
});

require base_path('core/router.php');

?>