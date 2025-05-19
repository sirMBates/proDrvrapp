<?php

define('BASE_PATH', __DIR__ . '/../');

define('HOME_PATH', __DIR__ . '/');

require BASE_PATH . 'core/helperfunc.php';

spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    require base_path("{$class}.php");
});

require base_path('core/router.php');

?>