<?php

requireLoginAjax();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: https://prodriver.local");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: X-CSRF-Token, Content-Type, X-Requested-With");

$headers = getallheaders();
$headerToken = $headers['X-CSRF-Token'] ?? null;
$sessionToken = $_SESSION['drvr_token'];

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

$method = $_SERVER['REQUEST_METHOD'];

if (!in_array($method, ['PATCH'])) {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed'
    ]);
    exit();
}

if ($method === 'POST' && isset($_POST['__method'])) {
    $override = strtoupper($_POST['__method']);
    $allowed  = ['PUT', 'PATCH', 'DELETE'];

    if (in_array($override, $allowed, true)) {
        $method = $override;
    }
}

if ($method === 'PATCH') {
    if (isset($_POST['confirm'])) {
        include_once base_path("app/models/assignmenthandlermodel.php");
        include_once base_path("app/errors/check_assignment.php");
        $driverId = htmlspecialchars(trim($_POST['driver_id']));
        $orderId = htmlspecialchars(trim($_POST['order_id']));
        $coachId = htmlspecialchars(trim($_POST['vehicle_id']));
        $confirmation = htmlspecialchars(trim($_POST['confirmed_assignment']));
        $confirmed = new UpdateAssignmentContr($driverId, $orderId, $coachId, $confirmation);
        $result = $confirmed->confirm();
        $isFetch = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        if ($isFetch) {
            echo json_encode($result);
            exit();
        }
    }
}

?>