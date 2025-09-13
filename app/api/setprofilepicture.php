<?php

requireLoginAjax();
header("Content-Type: application/json");

if (!in_array($method, ['PATCH'])) {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed'
    ]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
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
        'message' => 'Invalid CSRF token'
    ]);
    exit();
}

if ($method === 'PATCH') {
    if (isset($_FILES['profileImage'])) {
        include_once base_path("app/models/profilepicturemodel.php");
        include_once base_path("app/classes/set_profile_pic.php");
        $file = $_FILES['profileImage'];
        $drvrPicture = new SetDrvrPictureContr($file);
        $drvrPicture->setProfilePicture();
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Photo uploaded!'
        ]);
        exit();
    }
}

?>