<?php

declare(strict_types=1);

use App\Services\AccessKeyService;

require __DIR__ . '/src/bootstrap.php';

try {
    $accessKeyService = new AccessKeyService();

    $cycle = $accessKeyService->createInitialCycle();

    echo PHP_EOL;
    echo "ProDriver Initial Access Key Cycle" . PHP_EOL;
    echo "==================================" . PHP_EOL;
    echo "Cycle ID: {$cycle['cycle_id']}" . PHP_EOL;
    echo "Starts:   {$cycle['starts_at']}" . PHP_EOL;
    echo "Expires:  {$cycle['expires_at']}" . PHP_EOL;
    echo PHP_EOL;

    echo "Access Keys" . PHP_EOL;
    echo "-----------" . PHP_EOL;

    foreach ($cycle['keys'] as $role => $key) {
        echo strtoupper($role) . ': ' . $key['key'] . PHP_EOL;
    }
    echo PHP_EOL;
    echo "Initial access key cycle created successfully." . PHP_EOL;
} catch (\Throwable $exception) {
    fwrite(STDERR, PHP_EOL . "Access key bootstrap failed: " . $exception->getMessage() . PHP_EOL);
    exit(1);
}