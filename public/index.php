<?php

declare(strict_types=1);

$app = require dirname(__DIR__) . '/src/bootstrap.php';

if (PHP_SAPI === 'cli') {
    $app->handleCli($argv ?? []);
} else {
    $app->handleHttps();
}

?>