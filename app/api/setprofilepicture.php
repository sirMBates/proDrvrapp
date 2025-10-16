<?php

requireLoginAjax();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://prodriver.local");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: X-CSRF-Token, Content-Type, X-Requested-With");

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


$headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
$formToken = isset($_POST['drvrtoken']) ? htmlspecialchars(trim($_POST['drvrtoken'])) : null;
$sessionToken = $_SESSION['drvr_token'] ?? null;

if ($sessionToken === null) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'No session token found'
    ]);
    exit();
}

if ($formToken !== $sessionToken && $headerToken !== $sessionToken) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied'
    ]);
    exit();
}

if ($method === 'PATCH') {
    if (isset($_FILES['profileImage'])) {
        include_once base_path("app/models/getdrvrmodel.php");
        include_once base_path("app/models/profilepicturemodel.php");
        include_once base_path("app/errors/set_profile_pic.php");
        $file = $_FILES['profileImage'];
        $drvrPicture = new SetDrvrPictureContr($file);
        $result = $drvrPicture->setProfilePicture();
        $isFetch = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        if ($isFetch) {
            echo json_encode($result);
            exit();
        }
    }
}

?>