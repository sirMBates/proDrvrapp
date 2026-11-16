<?php

declare(strict_types=1);

use Core\Database;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);

    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed.'
    ]);
    exit();
}

try {
    $db = new Database();
    $pdo = $db->connect();

    $stmt = $pdo->query('SELECT 1');

    if ($stmt === false || $stmt->fetchColumn() === false) {
        throw new \RuntimeException('Database health check failed.');
    }

    http_response_code(200);
    echo json_encode([
        'status' => 'online'
    ]);
} catch (\Throwable $e) {
    error_log('ProDriver database health check failed: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode([
        'status' => 'degraded'
    ]);
}

exit();

?>