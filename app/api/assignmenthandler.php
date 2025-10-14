<?php

requireLoginAjax();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: https://prodriver.local");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: X-CSRF-Token, Content-Type, X-Requested-With");

$headers = getallheaders();
$headerToken = $headers['X-CSRF-Token'] ?? null;
$sessionToken = $_SESSION['drvr_token'];

if (!in_array($method, ['PATCH'])) {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed'
    ]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($_POST['__method'])) {
    $override = strtoupper($_POST['__method']);
    $allowed  = ['PUT', 'PATCH', 'DELETE'];

    if (in_array($override, $allowed, true)) {
        $method = $override;
    }
}

if ($sessionToken === null) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'No session token found'
    ]);
    exit();
}

if ($headerToken !== $sessionToken) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied' // Invalid CRSF Token
    ]);
    exit();
}

if ($method === 'PATCH') {}

?>