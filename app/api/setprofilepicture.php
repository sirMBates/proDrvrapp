<?php

declare(strict_types=1);

use App\Services\DriverProfileService;

$method = strtoupper($_POST['__method'] ?? $_SERVER['REQUEST_METHOD']);

if (!in_array($method, ['GET', 'PATCH'], true)) {
    http_response_code(405);
    exit();
}

$driverId = (int) ($_SESSION['driver_id'] ?? 0);
if ($driverId < 1) {
    http_response_code(401);
    exit();
}

if ($method === 'GET') {
    $profileService = new DriverProfileService();
    $driver = $profileService->driverProfile($driverId);
    $storedPath = $driver['profilePicture'] ?? null;

    if (!$storedPath) {
        http_response_code(404);
        exit();
    }

    $uploadRoot = realpath(BASE_PATH . 'storage/uploads');
    $filePath = realpath(BASE_PATH . 'storage/uploads/' . $storedPath);

    if ($uploadRoot === false || $filePath === false || !str_starts_with($filePath, $uploadRoot . DIRECTORY_SEPARATOR) || !is_file($filePath)) {
        http_response_code(404);
        exit();
    }

    $mimeType = mime_content_type($filePath);
    $allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/gif'
    ];

    if (!in_array($mimeType, $allowedTypes, true)) {
        http_response_code(415);
        exit();
    }

    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: private, no-cache, no-store, must-revalidate');

    readfile($filePath);
    exit();
}

if ($method === 'PATCH') {
    requireLoginAjax();
    header("Content-Type: application/json; charset=utf-8");

    $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    $formToken = isset($_POST['drvrtoken']) ? trim((string) $_POST['drvrtoken']) : null;
    $sessionToken = $_SESSION['drvr_token'] ?? null;

    if ($sessionToken === null) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'No session token found.'
        ]);
        exit();
    }

    $validHeaderToken = $headerToken !== null && hash_equals($sessionToken, $headerToken);
    $validFormToken = $formToken !== null && hash_equals($sessionToken, $formToken);

    if (!$validHeaderToken && !$validFormToken) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Access denied!'
        ]);
        exit();
    }

    if (!isset($_FILES['profileImage'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'No profile image was provided.'
        ]);
        exit();
    }

    include_once base_path("app/SubmissionHandlers/set_profile_pic.php");

    $file = $_FILES['profileImage'];
    $drvrPicture = new SetDrvrPictureContr($file);
    $result = $drvrPicture->setProfilePicture();

    echo json_encode($result);
    exit();
}

?>